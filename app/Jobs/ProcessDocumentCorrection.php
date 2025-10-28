<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http as HttpFacade;


class ProcessDocumentCorrection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $document;
    protected $documentId;
    public $timeout = 300;

    public function __construct(Document $document)
    {
        $this->document = $document->withoutRelations();
        $this->documentId = $document->id;
    }

    public function handle()
    {
        // Always operate on the live DB record (in case it was deleted while queued)
        $document = Document::find($this->documentId);
        if (! $document) {
            Log::warning("Document ID {$this->documentId} no longer exists; aborting job.");
            return;
        }

        // mark started (helps the UI know processing began and provides initial details)
        $this->pushProgress($document, 'Memulai pemrosesan dokumen...', 'Processing');

        // Resolve file location using the configured filesystem disk. In deployments like Railway
        // the app and worker don't share local disk, so we must support remote disks (s3) by
        // streaming the file to a temporary local path for processing.
        $fileLocation = $document->file_location;

        // Determine which disk actually contains the file. We try these in order:
        // 1. If the document record stores a disk (future-proof), try that.
        // 2. Iterate configured disks and pick the first one where the file exists.
        // 3. Fall back to the default disk.
        // For this deployment the worker should always fetch the original file from
        // the web server via a temporary signed URL rather than probing local
        // filesystem disks. This avoids cross-container filesystem assumptions.
        $disk = config('filesystems.default') ?: 'public';
        Log::info("Worker will fetch original via signed URL for Document ID {$document->id}");

        // Helper: stream the remote file into a temp file so downstream parsing
        // always operates on a local path. Caller must unlink the temp file when done.
        $tempFile = null;
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'doc_');
            $signedUrl = URL::temporarySignedRoute('correction.original', now()->addMinutes(10), ['document' => $document->id]);
            
            Log::info('Worker fetching file via signed URL', [
                'document_id' => $document->id,
                'signed_url' => $signedUrl,
                'app_url' => config('app.url'),
                'route_name' => 'correction.original',
                'expires_at' => now()->addMinutes(10)->timestamp
            ]);
            
            $response = HttpFacade::withOptions(['timeout' => 60, 'sink' => $tempFile])->get($signedUrl);

            $status = method_exists($response, 'status') ? $response->status() : null;
            $contentType = $response->header('Content-Type');
            $contentLength = $response->header('Content-Length');
            
            Log::info('Fallback download response', [
                'document_id' => $document->id,
                'status' => $status,
                'content_type' => $contentType,
                'content_length' => $contentLength,
                'temp_file_size' => file_exists($tempFile) ? filesize($tempFile) : 0
            ]);

            if (! ($response->successful() || $status === 200)) {
                $body = method_exists($response, 'body') ? $response->body() : null;
                Log::warning('Fallback download failed - non-200 status', ['document_id' => $document->id, 'status' => $status, 'body_snippet' => is_string($body) ? substr($body, 0, 500) : null]);
                @unlink($tempFile);
                $document->update(['upload_status' => 'Failed', 'details' => 'File tidak ditemukan oleh worker.']);
                return;
            }

            // Check if response is actually a PDF by content-type and file header
            if ($contentType && stripos($contentType, 'application/pdf') === false && stripos($contentType, 'text/html') !== false) {
                Log::warning('Fallback download returned HTML instead of PDF', [
                    'document_id' => $document->id,
                    'content_type' => $contentType,
                    'first_bytes' => file_exists($tempFile) ? bin2hex(substr(file_get_contents($tempFile, false, null, 0, 16), 0, 16)) : null
                ]);
                @unlink($tempFile);
                $document->update(['upload_status' => 'Failed', 'details' => 'Worker received HTML error page instead of PDF.']);
                return;
            }

            $file_path = $tempFile; // use the downloaded file
            Log::info("Fallback download successful for Document ID {$document->id}, using temp file: {$tempFile}", ['document_id' => $document->id]);

            // If the temp file has a MIME type of PDF but no .pdf extension, rename it
            try {
                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = @finfo_file($finfo, $file_path);
                    finfo_close($finfo);
                } else {
                    $mime = null;
                }

                $ext = pathinfo($file_path, PATHINFO_EXTENSION);
                if (strtolower($mime) === 'application/pdf' && strtolower($ext) !== 'pdf') {
                    $pdfPath = $file_path . '.pdf';
                    if (@rename($file_path, $pdfPath)) {
                        $file_path = $pdfPath;
                        $tempFile = $pdfPath; // ensure cleanup removes the renamed file
                        Log::info('Renamed temp download to have .pdf extension', ['document_id' => $document->id, 'new_path' => $pdfPath]);
                    } else {
                        Log::warning('Failed to rename temp file to .pdf extension', ['document_id' => $document->id, 'path' => $file_path]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Could not examine/rename downloaded temp file: ' . $e->getMessage(), ['document_id' => $document->id]);
            }
        } catch (\Throwable $e) {
            Log::warning('Fallback download via signed URL failed: ' . $e->getMessage(), ['document_id' => $document->id, 'exception' => $e->getTraceAsString()]);
            if (!empty($tempFile) && file_exists($tempFile)) @unlink($tempFile);
            $document->update(['upload_status' => 'Failed', 'details' => 'File tidak ditemukan oleh worker.']);
            return;
        }

        try {
            // DEBUG: surface the resolved local path and basic file checks so we can
            // diagnose "file not found by worker" issues in logs quickly.
            try {
                $debugExists = isset($file_path) && file_exists($file_path);
                $debugReadable = isset($file_path) && is_readable($file_path);
            } catch (\Throwable $t) {
                $debugExists = false;
                $debugReadable = false;
            }

            Log::info('Debug file path resolved', [
                'document_id' => $document->id,
                'disk' => $disk ?? null,
                'file_location' => $fileLocation ?? null,
                'file_path' => $file_path ?? null,
                'file_exists' => $debugExists,
                'is_readable' => $debugReadable,
            ]);

            // Quick validation: ensure the resolved file *looks* like a PDF by
            // checking the leading bytes for the "%PDF-" signature. This helps
            // detect cases where an HTML error page or truncated response was
            // saved to disk (common with signed-URL 502/502 pages) which would
            // otherwise cause the PDF parser to fail without an easy artifact.
            try {
                $isPdf = false;
                if (!empty($file_path) && is_file($file_path) && is_readable($file_path)) {
                    $h = @fopen($file_path, 'rb');
                    if ($h !== false) {
                        $first = @fread($h, 5);
                        @fclose($h);
                        if ($first === '%PDF-' || (is_string($first) && strpos($first, '%PDF') === 0)) {
                            $isPdf = true;
                        }
                    }
                }

                if (! $isPdf) {
                    // Save a small debug sample (first 1KB) to local storage for
                    // debugging. Do not fail noisily if the save itself errors.
                    try {
                        $sample = @file_get_contents($file_path, false, null, 0, 1024);
                        if ($sample !== false && !empty($sample)) {
                            $sampleName = 'debug_samples/document_' . $document->id . '_' . time() . '.sample.txt';
                            // Always attempt to save locally first so the worker keeps a copy
                            try {
                                Storage::disk('local')->put($sampleName, $sample);
                                Log::warning('PDF header missing; saved debug sample locally', ['document_id' => $document->id, 'sample' => $sampleName]);
                            } catch (\Throwable $e) {
                                Log::warning('PDF header missing; failed to save local debug sample: ' . $e->getMessage(), ['document_id' => $document->id]);
                            }

                            // S3/object-storage persistence intentionally removed —
                            // we only persist debug samples locally to avoid remote
                            // dependencies in this deployment.
                        }
                    } catch (\Throwable $_) {
                        // ignore sample saving failures
                    }

                    Log::error("Document Correction Failed for ID {$document->id}: Invalid PDF data: Missing `%PDF-` header.");
                    $document->update(['upload_status' => 'Failed', 'details' => 'Invalid PDF data: Missing %PDF header.']);

                    // cleanup temporary file if we created one
                    if (!empty($tempFile) && file_exists($tempFile)) {
                        @unlink($tempFile);
                    }

                    return;
                }
            } catch (\Throwable $e) {
                Log::warning('PDF header check failed: ' . $e->getMessage(), ['document_id' => $document->id]);
            }

            $parser = new Parser();
            // update progress for parsing
            $this->pushProgress($document, 'Membaca isi dokumen...');
            $pdf = $parser->parseFile($file_path);
            $original_text = trim($pdf->getText());

            if (empty($original_text)) {
                $document->update(['upload_status' => 'Failed', 'details' => 'Gagal mengekstrak teks dari PDF.']);
                return;
            }

            // Log PDF extraction stats for verification
            $pageCount = count($pdf->getPages());
            $originalLength = mb_strlen($original_text, 'UTF-8');
            Log::info('PDF extraction complete', [
                'document_id' => $document->id,
                'page_count' => $pageCount,
                'original_text_length' => $originalLength,
                'first_100_chars' => mb_substr($original_text, 0, 100, 'UTF-8'),
                'last_100_chars' => mb_substr($original_text, -100, 100, 'UTF-8')
            ]);

            $clean_text = mb_convert_encoding($original_text, 'UTF-8', 'UTF-8');
            $clean_text = preg_replace('/[[:cntrl:]]/', '', $clean_text);
            $original_text = $clean_text;
            
            // Verify no text was lost during cleaning
            $cleanedLength = mb_strlen($original_text, 'UTF-8');
            if ($cleanedLength < $originalLength * 0.9) {
                Log::warning('Significant text loss during cleaning', [
                    'document_id' => $document->id,
                    'original_length' => $originalLength,
                    'cleaned_length' => $cleanedLength,
                    'loss_percentage' => round((1 - $cleanedLength / $originalLength) * 100, 2)
                ]);
            }
            
            // indicate we're preparing chunks / checking cache
            $this->pushProgress($document, 'Mempersiapkan dokumen untuk dikoreksi...');

            $corrected_text = $this->correctTextWithGemini($original_text);

            if (str_starts_with($corrected_text, 'ERROR:')) {
                throw new \Exception($corrected_text);
            }

            // persist results and mark completed
            $document->original_text = $original_text;
            $document->corrected_text = $corrected_text;
            $document->upload_status = 'Completed';
            $this->pushProgress($document, 'Koreksi selesai.', 'Completed');
            $document->save();
            $document->fresh();

            // cleanup temporary file if we created one from remote storage
            if (!empty($tempFile) && file_exists($tempFile)) {
                @unlink($tempFile);
            }

            Log::info("Document ID {$document->id} corrected successfully.");

        } catch (\Exception $e) {
            Log::error("Document Correction Failed for ID {$document->id}: " . $e->getMessage());
            // cleanup temp file if used
            if (!empty($tempFile) && file_exists($tempFile)) {
                @unlink($tempFile);
            }

            $document->update(['upload_status' => 'Failed', 'details' => 'Pemrosesan gagal: ' . substr($e->getMessage(), 0, 250)]);
        }
    }

    private function correctTextWithGemini($text)
    {
        // Start timing for diagnostics
        $jobStart = microtime(true);

        try {
            $cacheKey = 'doc_correction_' . sha1($text);
            if (Cache::has($cacheKey)) {
                Log::info("Document correction cache hit for full document (key={$cacheKey}). Returning cached result.");
                return Cache::get($cacheKey);
            }

            $apiKey = env('GOOGLE_API_KEY');
            $modelName = 'gemini-2.5-flash';
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . $apiKey;

            // request timeout (seconds) - must be less than job timeout
            $timeoutDuration = 240;

            // Chunk size (characters) - tuneable
            $maxLength = 8000;
            $textLen = mb_strlen($text, 'UTF-8');
            $chunks = [];
            for ($offset = 0; $offset < $textLen; $offset += $maxLength) {
                $chunkText = mb_substr($text, $offset, $maxLength, 'UTF-8');
                $chunks[] = $chunkText;
            }

            $chunkCount = count($chunks);
            
            // Verify all text is captured in chunks
            $totalChunkLength = 0;
            foreach ($chunks as $chunk) {
                $totalChunkLength += mb_strlen($chunk, 'UTF-8');
            }
            
            Log::info("Processing document correction: length={$textLen} chars, chunks={$chunkCount}", [
                'document_id' => $this->documentId,
                'text_length' => $textLen,
                'chunk_count' => $chunkCount,
                'total_chunk_length' => $totalChunkLength,
                'coverage_percentage' => $textLen > 0 ? round(($totalChunkLength / $textLen) * 100, 2) : 0
            ]);

            // Prepare containers
            $correctedChunks = array_fill(0, $chunkCount, null);
            $toSend = [];
            $cacheHits = 0;

            // Per-chunk cache check
            foreach ($chunks as $i => $chunk) {
                $chunkKey = 'doc_chunk_' . sha1($chunk);
                if (Cache::has($chunkKey)) {
                    $correctedChunks[$i] = Cache::get($chunkKey);
                    $cacheHits++;
                } else {
                    $toSend[$i] = $chunk;
                }
            }

            Log::info("Chunk cache hits: {$cacheHits}/{$chunkCount}");

            // update document with chunking/cache summary so UI can show progress
            $document = Document::find($this->documentId);
            if (! $document) {
                Log::warning("Document ID {$this->documentId} not found when updating chunk/cache details; aborting.");
                return implode("\n\n", $correctedChunks);
            }

            $this->pushProgress($document, "Memeriksa dokumen ({$chunkCount} bagian)...");

            if (empty($toSend)) {
                $result = implode("\n\n", $correctedChunks);
                Cache::put($cacheKey, $result, now()->addDays(7));
                Log::info('All chunks served from cache; returning assembled result quickly.');
                return $result;
            }

            // Concurrency and batching
            $concurrency = 6; // tuneable; lower if you hit rate-limits
            $indices = array_keys($toSend);
            $batches = array_chunk($indices, $concurrency);
            $totalBatches = count($batches);
            $batchNumber = 0;

            foreach ($batches as $batch) {
                $batchNumber++;
                $batchStart = microtime(true);
                Log::info("Sending batch {$batchNumber}/{$totalBatches} (size=" . count($batch) . ") to Gemini...");

                // update progress so UI can display current batch being processed
                $document = Document::find($this->documentId);
                if (! $document) {
                    Log::warning("Document ID {$this->documentId} not found before sending batch {$batchNumber}; aborting.");
                    return implode("\n\n", $correctedChunks);
                }

                $this->pushProgress($document, "Mengoreksi bagian {$batchNumber} dari {$totalBatches}...");

                // Prepare payload descriptors so we can match responses to indices
                $batchChunks = [];
                foreach ($batch as $idx) {
                    $batchChunks[] = ['index' => $idx, 'text' => $toSend[$idx]];
                }

                // Attempt the batch via Http::pool. Wrap to catch unexpected exceptions
                $responses = [];
                try {
                    Log::info("About to call Http::pool for batch {$batchNumber}", [
                        'document_id' => $this->documentId,
                        'batch_size' => count($batchChunks),
                        'timeout' => $timeoutDuration
                    ]);
                    
                    $poolResponses = Http::pool(function (Pool $pool) use ($url, $batchChunks, $timeoutDuration) {
                        $calls = [];
                        foreach ($batchChunks as $b) {
                            $payload = [
                                'contents' => [
                                    [
                                        'parts' => [
                                            ['text' => "Perbaiki tata bahasa dan ejaan dalam bahasa Indonesia tanpa mengubah makna berikut. Jangan ubah format tata letak teksnya. Berikan dalam bentuk teks saja, dan hanya berikan teks hasilnya.\n\n" . $b['text']]
                                        ]
                                    ]
                                ]
                            ];
                            $calls[] = $pool->withOptions(['timeout' => $timeoutDuration])->post($url, $payload);
                        }
                        return $calls;
                    });
                    
                    Log::info("Http::pool returned", [
                        'document_id' => $this->documentId,
                        'batch' => $batchNumber,
                        'response_count' => count($poolResponses)
                    ]);
                    
                    // Convert pool responses - pool can return exceptions instead of responses
                    foreach ($poolResponses as $idx => $poolResp) {
                        if ($poolResp instanceof \Throwable) {
                            Log::error("Pool response {$idx} is exception", [
                                'class' => get_class($poolResp),
                                'message' => $poolResp->getMessage()
                            ]);
                            // Pool returned an exception - create a synthetic failed response
                            $responses[] = new class {
                                public function successful() { return false; }
                                public function status() { return 0; }
                                public function body() { return 'pool request threw exception'; }
                                public function json() { return []; }
                            };
                        } else {
                            $status = method_exists($poolResp, 'status') ? $poolResp->status() : 'unknown';
                            Log::info("Pool response {$idx} is valid", [
                                'status' => $status,
                                'successful' => method_exists($poolResp, 'successful') ? $poolResp->successful() : 'unknown'
                            ]);
                            $responses[] = $poolResp;
                        }
                    }
                    
                    Log::info("Http::pool processed responses", [
                        'document_id' => $this->documentId,
                        'batch' => $batchNumber,
                        'total' => count($responses),
                        'successful' => count(array_filter($responses, fn($r) => method_exists($r, 'successful') && $r->successful()))
                    ]);
                    
                } catch (\Throwable $t) {
                    Log::error("Http::pool failed on batch {$batchNumber}: " . $t->getMessage(), [
                        'exception_class' => get_class($t),
                        'trace' => $t->getTraceAsString()
                    ]);
                    // Fallback: process sequentially with retries
                    $responses = [];
                    foreach ($batchChunks as $b) {
                        try {
                            $resp = $this->sendChunkWithRetries($url, $b['text'], $timeoutDuration);
                            $responses[] = $resp;
                        } catch (\Throwable $e) {
                            Log::error("sendChunkWithRetries threw exception for chunk {$b['index']}: " . $e->getMessage());
                            // Create a synthetic failed response for this chunk
                            $responses[] = new class {
                                public function successful() { return false; }
                                public function status() { return 0; }
                                public function body() { return 'exception during retry'; }
                                public function json() { return []; }
                            };
                        }
                    }
                }

                // Handle responses in order
                Log::info("Processing responses for batch {$batchNumber}", [
                    'document_id' => $this->documentId,
                    'response_count' => count($responses)
                ]);
                
                foreach (array_values($responses) as $k => $response) {
                    $b = $batchChunks[$k];
                    $index = $b['index'];
                    
                    Log::info("Processing response for chunk {$index}", [
                        'document_id' => $this->documentId,
                        'batch_index' => $k,
                        'chunk_index' => $index,
                        'response_type' => is_object($response) ? get_class($response) : gettype($response)
                    ]);
                    
                    // Check if response is an exception/error instead of a proper response
                    if ($response instanceof \Throwable) {
                        Log::error("Chunk {$index} got exception from pool", [
                            'exception_class' => get_class($response),
                            'message' => $response->getMessage()
                        ]);
                        $correctedChunks[$index] = "[GAGAL KOREKSI BAGIAN {$index}]";
                        continue;
                    }
                    
                    // Check if response is actually a Response object
                    if (! is_object($response) || ! method_exists($response, 'successful')) {
                        Log::error("Invalid response object for chunk {$index}", [
                            'type' => is_object($response) ? get_class($response) : gettype($response)
                        ]);
                        $correctedChunks[$index] = "[GAGAL KOREKSI BAGIAN {$index}]";
                        continue;
                    }

                    if (! $response->successful()) {
                        $status = method_exists($response, 'status') ? $response->status() : 'unknown';
                        Log::error("Gemini HTTP Error (Chunk {$index}): status={$status} body=" . substr($response->body(), 0, 500));
                        $correctedChunks[$index] = "[GAGAL KOREKSI BAGIAN {$index}]";
                        continue;
                    }

                    // Successful response: try to extract corrected text
                    try {
                        $body = method_exists($response, 'body') ? $response->body() : (string) $response;
                        $extracted = null;
                        try {
                            $json = $response->json();
                            Log::info("Gemini response JSON for chunk {$index}", [
                                'document_id' => $this->documentId,
                                'has_candidates' => isset($json['candidates']),
                                'candidate_count' => isset($json['candidates']) ? count($json['candidates']) : 0,
                                'json_keys' => is_array($json) ? array_keys($json) : []
                            ]);
                            
                            if (is_array($json)) {
                                // Gemini API response structure (updated for 2.5-flash):
                                // Try: candidates[0].content.parts[0].text (current API format)
                                if (!empty($json['candidates'][0]['content']['parts'][0]['text'])) {
                                    $extracted = $json['candidates'][0]['content']['parts'][0]['text'];
                                    Log::info("Extracted via candidates[0].content.parts[0].text for chunk {$index}");
                                }
                                // Fallback: candidates[0].output (older format)
                                elseif (!empty($json['candidates'][0]['output'])) {
                                    $extracted = $json['candidates'][0]['output'];
                                    Log::info("Extracted via candidates[0].output for chunk {$index}");
                                }
                                // Fallback: top-level output
                                elseif (!empty($json['output'])) {
                                    $extracted = is_string($json['output']) ? $json['output'] : json_encode($json['output']);
                                    Log::info("Extracted via top-level output for chunk {$index}");
                                }
                            }
                        } catch (\Throwable $jsonErr) {
                            Log::error("JSON parsing failed for chunk {$index}: " . $jsonErr->getMessage());
                        }

                        if (empty($extracted)) {
                            Log::warning("No text extracted from JSON, using raw body for chunk {$index}", [
                                'body_length' => strlen($body),
                                'body_preview' => substr($body, 0, 200)
                            ]);
                            $extracted = trim($body);
                        }
                        
                        $correctedChunks[$index] = trim($extracted);
                        Log::info("Successfully processed chunk {$index}", [
                            'document_id' => $this->documentId,
                            'extracted_length' => mb_strlen($correctedChunks[$index], 'UTF-8')
                        ]);

                        // cache this chunk for future runs
                        try {
                            $chunkKey = 'doc_chunk_' . sha1($b['text']);
                            Cache::put($chunkKey, $correctedChunks[$index], now()->addDays(7));
                        } catch (\Throwable $_) {
                            // ignore cache failures
                        }
                    } catch (\Throwable $t) {
                        Log::warning("Failed to parse Gemini response for chunk {$index}: " . $t->getMessage());
                        $correctedChunks[$index] = "[GAGAL KOREKSI BAGIAN {$index}]";
                    }
                }
            }

            // All batches processed; assemble and return
            $document = Document::find($this->documentId);
            if (! $document) {
                Log::warning("Document ID {$this->documentId} not found before assembling; aborting.");
                return implode("\n\n", $correctedChunks);
            }

            $this->pushProgress($document, 'Menggabungkan hasil koreksi...');

            $result = implode("\n\n", $correctedChunks);
            
            // Verify all chunks were processed
            $nullChunks = 0;
            $failedChunks = 0;
            foreach ($correctedChunks as $idx => $chunk) {
                if ($chunk === null) $nullChunks++;
                if (is_string($chunk) && strpos($chunk, '[GAGAL KOREKSI BAGIAN') === 0) $failedChunks++;
            }
            
            Log::info("Document correction assembly complete", [
                'document_id' => $this->documentId,
                'total_chunks' => $chunkCount,
                'null_chunks' => $nullChunks,
                'failed_chunks' => $failedChunks,
                'result_length' => mb_strlen($result, 'UTF-8'),
                'original_length' => $textLen
            ]);
            
            Cache::put($cacheKey, $result, now()->addDays(7));

            $totalTook = round(microtime(true) - $jobStart, 3);
            Log::info("Document correction finished: chunks={$chunkCount}, total_time={$totalTook}s");

            return $result;

        } catch (\Exception $e) {
            Log::error('Gemini Request Exception (Job): ' . $e->getMessage());
            return "ERROR: " . $e->getMessage();
        }
    }

    /**
     * Send a single chunk with a small retry loop for transient errors.
     * Returns a Response-like object (Laravel HTTP client response) or a small shim.
     */
    private function sendChunkWithRetries(string $url, string $text, int $timeoutDuration)
    {
        $attempts = 0;
        $maxAttempts = 2;
        $lastResponse = null;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                $payload = [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => "Perbaiki tata bahasa dan ejaan dalam bahasa Indonesia tanpa mengubah makna berikut. Jangan ubah format tata letak teksnya. Berikan dalam bentuk teks saja, dan hanya berikan teks hasilnya.\n\n" . $text]
                            ]
                        ]
                    ]
                ];

                $response = Http::withOptions(['timeout' => $timeoutDuration])->post($url, $payload);
                if ($response->successful()) {
                    return $response;
                }
                $lastResponse = $response;
                Log::warning("Chunk request attempt {$attempts} failed (status=" . $response->status() . ").");
            } catch (\Throwable $t) {
                Log::warning("Chunk request attempt {$attempts} exception: " . $t->getMessage());
            }

            // backoff between attempts
            sleep(1);
        }

        // If we reach here return last response or a synthetic failed response
        if ($lastResponse) {
            return $lastResponse;
        }

        // Create a synthetic response shim for uniform handling
        return new class {
            public function successful() { return false; }
            public function status() { return 0; }
            public function body() { return 'no-response'; }
            public function json() { return []; }
        };
    }

    /**
     * Append a progress entry to the document's progress_log and update `details`.
     * This is best-effort and will not throw if the document is gone.
     */
    private function pushProgress(Document $document, string $message, string $status = null)
    {
        try {
            $log = $document->progress_log ?? [];
            if (!is_array($log)) $log = [];
            $entry = ['ts' => now()->toDateTimeString(), 'message' => $message];
            $log[] = $entry;
            // keep last 50 entries only
            if (count($log) > 50) $log = array_slice($log, -50);

            $update = ['progress_log' => $log, 'details' => $message];
            if (!is_null($status)) {
                $update['upload_status'] = $status;
            }

            $document->update($update);
        } catch (\Throwable $e) {
            Log::warning("pushProgress failed for Document ID {$document->id}: " . $e->getMessage());
        }
    }
}