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
        $file_path = storage_path("app/public/{$document->file_location}");
        
        if (!file_exists($file_path)) {
            $document->update(['upload_status' => 'Failed', 'details' => 'File tidak ditemukan oleh worker.']);
            return;
        }

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($file_path);
            $original_text = trim($pdf->getText());

            if (empty($original_text)) {
                $document->update(['upload_status' => 'Failed', 'details' => 'Gagal mengekstrak teks dari PDF.']);
                return;
            }

            $clean_text = mb_convert_encoding($original_text, 'UTF-8', 'UTF-8');
            $clean_text = preg_replace('/[[:cntrl:]]/', '', $clean_text);
            $original_text = $clean_text;
            $corrected_text = $this->correctTextWithGemini($original_text);

            if (str_starts_with($corrected_text, 'ERROR:')) {
                throw new \Exception($corrected_text);
            }

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
        try {
            $apiKey = env('GOOGLE_API_KEY');
            $modelName = 'gemini-2.5-flash'; 
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . $apiKey;
            $timeoutDuration = 0; // Mengandalkan timeout Job Laravel (300s)

            // 🔹 Batas panjang per chunk (Gemini biasanya aman di < 7000 karakter)
            $maxLength = 7000;
            $chunks = str_split($text, $maxLength);

            $correctedChunks = [];

            foreach ($chunks as $index => $chunk) {
                \Log::info("🟦 Sending chunk " . ($index + 1) . "/" . count($chunks) . " to Gemini...");

            $response = Http::timeout($timeoutDuration)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "Perbaiki tata bahasa dan ejaan dalam bahasa Indonesia tanpa mengubah makna berikut. Jangan ubah format tata letak teksnya. Berikan dalam bentuk teks saja, dan hanya berikan teks hasilnya.\n\n" . $chunk]
                        ]
                    ]
                ]
            ]);

                $data = $response->json();

                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $correctedText = $data['candidates'][0]['content']['parts'][0]['text'];
                    $correctedChunks[] = trim($correctedText);
                } else {
                    $errorMessage = $data['error']['message'] 
                        ?? ($data['candidates'][0]['finishReason'] ?? 'Tidak ada teks hasil koreksi dari Gemini.');
                    \Log::error("Gemini API Error (Chunk {$index}): " . $errorMessage);
                    $correctedChunks[] = "[GAGAL KOREKSI BAGIAN {$index}]";
                }

                // Optional: delay kecil biar API nggak overload
                sleep(2);
            }

            // Gabungkan semua hasil jadi satu teks
            return implode("\n\n", $correctedChunks);

        } catch (\Exception $e) {
            \Log::error('Gemini Request Exception (Job): ' . $e->getMessage());
            return "ERROR: " . $e->getMessage();
        }
    }
}