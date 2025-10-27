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


class ProcessDocumentCorrection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $document;
    public $timeout = 300;

    public function __construct(Document $document)
    {
        $this->document = $document->withoutRelations();
    }

    public function handle()
    {
        $document = $this->document;
        // mark started (helps the UI know processing began and provides initial details)
        try {
            $document->update(['upload_status' => 'Processing', 'details' => 'Memulai pemrosesan dokumen...']);
        } catch (\Throwable $e) {
            // best-effort: log and continue; model fillable has been updated to include details
            Log::warning("Could not write initial processing status for Document ID {$document->id}: " . $e->getMessage());
        }
        $file_path = storage_path("app/public/{$document->file_location}");
        
        if (!file_exists($file_path)) {
            $document->update(['upload_status' => 'Failed', 'details' => 'File tidak ditemukan oleh worker.']);
            return;
        }

        try {
            $parser = new Parser();
            // update progress for parsing
            try {
                $document->update(['details' => 'Memulai parsing PDF...']);
            } catch (\Throwable $e) {
                Log::warning("Could not update parsing details for Document ID {$document->id}: " . $e->getMessage());
            }
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
            try {
                $document->update(['details' => 'Memecah dokumen menjadi potongan dan memeriksa cache...']);
            } catch (\Throwable $e) {
                Log::warning("Could not update chunking details for Document ID {$document->id}: " . $e->getMessage());
            }

            $corrected_text = $this->correctTextWithGemini($original_text);

            if (str_starts_with($corrected_text, 'ERROR:')) {
                throw new \Exception($corrected_text);
            }

            // persist results and mark completed
            $document->original_text = $original_text;
            $document->corrected_text = $corrected_text;
            $document->upload_status = 'Completed';
            $document->details = 'Koreksi selesai.';
            $document->save();
            $document->fresh();

            Log::info("Document ID {$document->id} corrected successfully.");

        } catch (\Exception $e) {
            Log::error("Document Correction Failed for ID {$document->id}: " . $e->getMessage());
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
            try {
                $this->document->update(['details' => "Memproses dokumen: panjang={$textLen} chars, potongan={$chunkCount}, cache_hits={$cacheHits}"]);
            } catch (\Throwable $e) {
                Log::warning("Could not update chunk/cache details for Document ID {$this->document->id}: " . $e->getMessage());
            }

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
                try {
                    $this->document->update(['details' => "Mengirim batch {$batchNumber}/{$totalBatches} ke Gemini (size=" . count($batch) . ")"]);
                } catch (\Throwable $e) {
                    Log::warning("Could not update batch details for Document ID {$this->document->id}: " . $e->getMessage());
                }

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

                    $data = $response->json();
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        $correctedText = trim($data['candidates'][0]['content']['parts'][0]['text']);
                        $correctedChunks[$index] = $correctedText;
                        $chunkKey = 'doc_chunk_' . sha1($b['text']);
                        Cache::put($chunkKey, $correctedText, now()->addDays(30));
                        Log::info("Chunk {$index} corrected and cached (chunkKey={$chunkKey}).");
                    } else {
                        $errorMessage = $data['error']['message'] ?? ($data['candidates'][0]['finishReason'] ?? 'Tidak ada teks hasil koreksi dari Gemini.');
                        Log::error("Gemini API Error (Chunk {$index}): " . $errorMessage);
                        $correctedChunks[$index] = "[GAGAL KOREKSI BAGIAN {$index}]";
                    }
                }

                $batchTook = round(microtime(true) - $batchStart, 3);
                Log::info("Batch {$batchNumber}/{$totalBatches} completed in {$batchTook}s.");

                // small pause between batches if you need to respect rate limits
                // usleep(150000);
            }

            // Assemble final result and cache
            // indicate assembly step
            try {
                $this->document->update(['details' => 'Menggabungkan hasil koreksi...']);
            } catch (\Throwable $e) {
                Log::warning("Could not update assembling details for Document ID {$this->document->id}: " . $e->getMessage());
            }

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
}