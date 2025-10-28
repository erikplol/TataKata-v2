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
        $disk = null;
        $candidateDisks = [];

        // Prefer an explicit disk saved on the Document, then prefer 'public' (local
        // shared storage), and finally the other configured disks. We intentionally
        // skip any disk literally named 's3' to avoid probing S3 when you don't
        // want to use object storage in this deployment.
        if (!empty($document->disk)) {
            $candidateDisks[] = $document->disk;
        }

        if (!in_array('public', $candidateDisks, true)) {
            $candidateDisks[] = 'public';
        }

        foreach (array_keys(config('filesystems.disks') ?? []) as $cfgDisk) {
            if ($cfgDisk === 's3') continue; // skip s3 by request
            if (!in_array($cfgDisk, $candidateDisks, true)) {
                $candidateDisks[] = $cfgDisk;
            }
        }

        foreach ($candidateDisks as $candidate) {
            try {
                if (empty($candidate)) continue;
                if (Storage::disk($candidate)->exists($fileLocation)) {
                    $disk = $candidate;
                    break;
                }
            } catch (\Throwable $e) {
                // ignore misconfigured disk adapters and continue
                Log::warning("Storage disk check failed for candidate '{$candidate}': " . $e->getMessage());
                continue;
            }
        }

        if (empty($disk)) {
            // fallback to configured default
            $disk = config('filesystems.default');
        }

        Log::info("Resolved file disk for Document ID {$document->id}: {$disk}");

        // Helper: get a usable local path to the uploaded file. If the disk exposes a local path
        // return it; otherwise stream the file to a temp file and return that path. Caller must
        // unlink the temp file when done if one is created.
        $tempFile = null;
        try {
            if (Storage::disk($disk)->exists($fileLocation)) {
                // If the disk is local (public/local), we can get the real path
                // Storage::disk(...)->path() works for local drivers.
                try {
                    $file_path = Storage::disk($disk)->path($fileLocation);
                } catch (\Exception $e) {
                    // Some drivers (s3) don't support path(); fall back to stream copy below
                    Log::info("Storage::path not available for disk {$disk}, will attempt readStream.", ['document_id' => $document->id, 'exception' => $e->getMessage()]);
                    $file_path = null;
                }

                // If we couldn't get a native path or the path doesn't exist, stream to a temp file
                if (empty($file_path) || !file_exists($file_path)) {
                    // stream to temp
                    $stream = null;
                    try {
                        $stream = Storage::disk($disk)->readStream($fileLocation);
                    } catch (\Throwable $e) {
                        Log::warning("Storage::readStream threw for disk {$disk} file {$fileLocation}: " . $e->getMessage(), ['document_id' => $document->id]);
                        $stream = false;
                    }

                    if ($stream === false) {
                        Log::error("readStream returned false for disk={$disk} file={$fileLocation}", ['document_id' => $document->id]);
                        $document->update(['upload_status' => 'Failed', 'details' => 'File tidak dapat dibaca oleh worker.']);
                        return;
                    }

                    $tempFile = tempnam(sys_get_temp_dir(), 'doc_');
                    $out = fopen($tempFile, 'w');
                    $bytes = stream_copy_to_stream($stream, $out);
                    fclose($out);
                    if (is_resource($stream)) fclose($stream);

                    Log::info('Streamed remote/local disk file into temp file', ['document_id' => $document->id, 'disk' => $disk, 'file_location' => $fileLocation, 'tempFile' => $tempFile, 'bytes_copied' => $bytes]);

                    $file_path = $tempFile;
                } else {
                    Log::info('Resolved native file path for document', ['document_id' => $document->id, 'disk' => $disk, 'file_path' => $file_path]);
                }
            } else {
                // As a fallback when the worker cannot read local storage (separate
                // containers), attempt to fetch the original via a temporary signed
                // URL from the web app. This requires the `correction.original` route
                // to accept signed requests (handled in the controller).
                try {
                    $tempFile = tempnam(sys_get_temp_dir(), 'doc_');
                    $signedUrl = URL::temporarySignedRoute('correction.original', now()->addMinutes(10), ['document' => $document->id]);

                    // Stream the remote file into the temp file to avoid memory pressure
                    $response = HttpFacade::withOptions(['timeout' => 60, 'sink' => $tempFile])->get($signedUrl);

                    $status = method_exists($response, 'status') ? $response->status() : null;
                    if (! ($response->successful() || $status === 200)) {
                        $body = method_exists($response, 'body') ? $response->body() : null;
                        Log::warning('Fallback download failed', ['document_id' => $document->id, 'signed_url' => $signedUrl, 'status' => $status, 'body_snippet' => is_string($body) ? substr($body, 0, 500) : null]);
                        @unlink($tempFile);
                        $document->update(['upload_status' => 'Failed', 'details' => 'File tidak ditemukan oleh worker.']);
                        return;
                    }

                    $file_path = $tempFile; // use the downloaded file
                    Log::info("Fallback download successful for Document ID {$document->id}, using temp file: {$tempFile}", ['document_id' => $document->id]);

                    // If the temp file has no .pdf extension but its MIME type is PDF,
                    // rename it to include .pdf so downstream tools that rely on
                    // extensions (or for easier debugging) see a proper filename.
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

                    // Persist the downloaded file to object storage (prefer 's3') so
                    // the web process and users can access it via MinIO/S3 while the
                    // worker parses the local temp copy.
                    try {
                        // Persist the downloaded file to local/public storage only.
                        // We intentionally avoid S3/object storage in this branch per
                        // configuration: prefer the Document.recorded disk or the
                        // application default and fall back to 'public'.
                        $targetDisk = $document->disk ?: config('filesystems.default');
                        if (empty($targetDisk)) {
                            $targetDisk = 'public';
                        }

                        try {
                            // Before streaming into the adapter, prefer doing a direct
                            // copy when the target disk is a local driver. This avoids
                            // stream-related truncation issues and writes the file to
                            // the correct storage path (e.g., storage/app/public/...).
                            $cfg = config('filesystems.disks.' . $targetDisk, []);
                            $isLocalDriver = isset($cfg['driver']) && $cfg['driver'] === 'local';

                            if ($isLocalDriver) {
                                try {
                                    $dir = pathinfo($fileLocation, PATHINFO_DIRNAME);
                                    if ($dir === '.' || $dir === '') $dir = '';
                                    $basename = pathinfo($fileLocation, PATHINFO_BASENAME);
                                    // Use Illuminate\Http\File to ensure the filesystem
                                    // copy uses the real file and not a stream.
                                    $fileObj = new \Illuminate\Http\File($file_path);
                                    Storage::disk($targetDisk)->putFileAs($dir, $fileObj, $basename);
                                    Log::info('Copied temp file into local disk via putFileAs', ['document_id' => $document->id, 'disk' => $targetDisk, 'file_location' => $fileLocation]);
                                } catch (\Throwable $e) {
                                    Log::warning('Local disk copy failed; will fall back to streamed upload: ' . $e->getMessage(), ['document_id' => $document->id]);
                                    $isLocalDriver = false; // allow fallback to stream path
                                }
                            }

                            if (! $isLocalDriver) {
                                // Open in binary mode and ensure the stream pointer is at
                                // the start. Some drivers expect a fresh stream or a
                                // rewound stream; failing to rewind can result in
                                // truncated/corrupt uploads.
                                $stream = @fopen($file_path, 'rb');
                                if ($stream !== false) {
                                    // Ensure pointer is at start
                                    if (is_resource($stream) && ftell($stream) !== 0) {
                                        rewind($stream);
                                    }

                                    // Determine local size before attempting upload so we
                                    // can detect truncation after the put.
                                    $localSize = is_file($file_path) ? filesize($file_path) : null;
                                    Log::info('Preparing to persist file', ['document_id' => $document->id, 'local_size' => $localSize]);

                                    // Detect MIME type and pass it to the storage adapter so
                                    // object storage saves an accurate Content-Type header.
                                    $mime = null;
                                    try {
                                        if (function_exists('finfo_open')) {
                                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                            $mime = @finfo_file($finfo, $file_path) ?: null;
                                            finfo_close($finfo);
                                        }
                                    } catch (\Throwable $_) {
                                        $mime = null;
                                    }

                                    $putOptions = [];
                                    if (!empty($mime)) {
                                        $putOptions['ContentType'] = $mime;
                                    }
                                    // prefer public visibility so files are easier to inspect
                                    $putOptions['visibility'] = $putOptions['visibility'] ?? 'public';

                                    $putResult = Storage::disk($targetDisk)->put($fileLocation, $stream, $putOptions);

                                    // close stream after upload
                                    if (is_resource($stream)) fclose($stream);

                                    Log::info('Persisted fallback-downloaded file to disk (stream)', ['document_id' => $document->id, 'disk' => $targetDisk, 'file_location' => $fileLocation, 'content_type' => $mime, 'put_result' => $putResult]);
                                } else {
                                    Log::warning('Could not open file for persistence', ['document_id' => $document->id, 'path' => $file_path]);
                                }
                            }

                                // Verify sizes match when possible to detect corruption
                                try {
                                    $localSize = is_file($file_path) ? filesize($file_path) : null;
                                    $remoteSize = null;
                                    if (!is_null($localSize)) {
                                        $remoteSize = Storage::disk($targetDisk)->size($fileLocation);
                                    }

                                    if (!is_null($localSize) && !is_null($remoteSize) && $localSize !== $remoteSize) {
                                        Log::warning('Size mismatch after persistence; attempting memory fallback upload', ['document_id' => $document->id, 'local' => $localSize, 'remote' => $remoteSize]);

                                        // Fallback: read into memory and retry (safe for typical PDF sizes; adjust if you expect very large files)
                                        try {
                                            $contents = @file_get_contents($file_path);
                                            if ($contents !== false) {
                                                $memOptions = [];
                                                if (!empty($mime)) $memOptions['ContentType'] = $mime;
                                                $memOptions['visibility'] = $memOptions['visibility'] ?? 'public';
                                                Storage::disk($targetDisk)->put($fileLocation, $contents, $memOptions);
                                                Log::info('Fallback memory upload succeeded', ['document_id' => $document->id, 'disk' => $targetDisk, 'file_location' => $fileLocation, 'content_type' => $mime]);
                                            } else {
                                                Log::warning('Fallback memory upload failed: could not read local file', ['document_id' => $document->id]);
                                            }
                                        } catch (\Throwable $e) {
                                            Log::warning('Fallback memory upload threw: ' . $e->getMessage(), ['document_id' => $document->id]);
                                        }
                                    }
                                } catch (\Throwable $_) {
                                    // ignore size check failures
                                }

                                if ($document->disk !== $targetDisk) {
                                    $document->disk = $targetDisk;
                                    $document->save();
                                }
                        } catch (\Throwable $e) {
                            Log::warning('Failed to persist fallback-downloaded file: ' . $e->getMessage(), ['document_id' => $document->id]);
                        }
                    } catch (\Throwable $_) {
                        // ignore persistence failures but continue parsing
                    }
                } catch (\Throwable $e) {
                    Log::warning('Fallback download via signed URL failed: ' . $e->getMessage(), ['document_id' => $document->id, 'exception' => $e->getTraceAsString()]);
                    if (!empty($tempFile) && file_exists($tempFile)) @unlink($tempFile);
                    $document->update(['upload_status' => 'Failed', 'details' => 'File tidak ditemukan oleh worker.']);
                    return;
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error resolving file for Document ID {$document->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $document->update(['upload_status' => 'Failed', 'details' => 'File tidak dapat diakses oleh worker.']);
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
            $this->pushProgress($document, 'Memulai parsing PDF...');
            $pdf = $parser->parseFile($file_path);
            $original_text = trim($pdf->getText());

            if (empty($original_text)) {
                $document->update(['upload_status' => 'Failed', 'details' => 'Gagal mengekstrak teks dari PDF.']);
                return;
            }

            $clean_text = mb_convert_encoding($original_text, 'UTF-8', 'UTF-8');
            $clean_text = preg_replace('/[[:cntrl:]]/', '', $clean_text);
            $original_text = $clean_text;
            // indicate we're preparing chunks / checking cache
            $this->pushProgress($document, 'Memecah dokumen menjadi potongan dan memeriksa cache...');

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
                $chunks[] = mb_substr($text, $offset, $maxLength, 'UTF-8');
            }

            $chunkCount = count($chunks);
            Log::info("Processing document correction: length={$textLen} chars, chunks={$chunkCount}");

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

            $this->pushProgress($document, "Memproses dokumen: panjang={$textLen} chars, potongan={$chunkCount}, cache_hits={$cacheHits}");

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

                $this->pushProgress($document, "Mengirim batch {$batchNumber}/{$totalBatches} ke Gemini (size=" . count($batch) . ")");

                // Prepare payload descriptors so we can match responses to indices
                $batchChunks = [];
                foreach ($batch as $idx) {
                    $batchChunks[] = ['index' => $idx, 'text' => $toSend[$idx]];
                }

                // Attempt the batch via Http::pool. Wrap to catch unexpected exceptions
                try {
                    $responses = Http::withOptions(['timeout' => $timeoutDuration])->pool(function (Pool $pool) use ($url, $batchChunks) {
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
                            $calls[] = $pool->post($url, $payload);
                        }
                        return $calls;
                    });
                } catch (\Throwable $t) {
                    Log::error("Http::pool failed on batch {$batchNumber}: " . $t->getMessage());
                    // Fallback: process sequentially with retries
                    $responses = [];
                    foreach ($batchChunks as $b) {
                        $resp = $this->sendChunkWithRetries($url, $b['text'], $timeoutDuration);
                        $responses[] = $resp;
                    }
                }

                // Handle responses in order
                foreach (array_values($responses) as $k => $response) {
                    $b = $batchChunks[$k];
                    $index = $b['index'];

                    if (! $response->successful()) {
                        $status = method_exists($response, 'status') ? $response->status() : 'unknown';
                        Log::error("Gemini HTTP Error (Chunk {$index}): status={$status} body=" . $response->body());
                        $correctedChunks[$index] = "[GAGAL KOREKSI BAGIAN {$index}]";
                        continue;
                    }

                    // Successful response: try to extract corrected text
                    try {
                        $body = method_exists($response, 'body') ? $response->body() : (string) $response;
                        $extracted = null;
                        try {
                            $json = $response->json();
                            if (is_array($json)) {
                                // Common Gemini-like shapes: try candidates -> output or top-level output
                                if (!empty($json['candidates'][0]['output'])) {
                                    $extracted = $json['candidates'][0]['output'];
                                } elseif (!empty($json['output'])) {
                                    $extracted = is_string($json['output']) ? $json['output'] : json_encode($json['output']);
                                }
                            }
                        } catch (\Throwable $_) {
                            // ignore JSON parsing errors
                        }

                        if (empty($extracted)) $extracted = trim($body);
                        $correctedChunks[$index] = trim($extracted);

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