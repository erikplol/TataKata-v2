<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FastAPIController extends Controller
{
    private $fastApiBase;

    public function __construct()
    {
        $this->fastApiBase = env('AI_URL');
    }

    // Universal helper
    private function callFastAPI($method, $endpoint, $data = [], $file = null)
    {
        try {
            $url = "{$this->fastApiBase}{$endpoint}";

            if ($file) {
                $response = Http::attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )->post($url);
            } else {
                $response = Http::send($method, $url, ['json' => $data]);
            }

            return $response->json();

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal terhubung ke FastAPI service',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // Hybrid grammar + AI check
    public function checkHybrid(Request $request)
    {
        return $this->callFastAPI('POST', '/api/correct-text', [
            'text' => $request->input('text')
        ]);
    }

    // Upload PDF to FastAPI
    public function uploadPDF(Request $request)
    {
        $file = $request->file('file');
        if (!$file) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        return $this->callFastAPI('POST', '/api/correct-pdf', [], $file);
    }

    // Custom AI prediction
    public function predictAI(Request $request)
    {
        return $this->callFastAPI('POST', '/api/ai/check', [
            'text' => $request->input('text')
        ]);
    }

    // // Fill-mask model (HuggingFace)
    // public function predictMask(Request $request)
    // {
    //     return $this->callFastAPI('POST', '/api/predict', [
    //         'text' => $request->input('text')
    //     ]);
    // }

    // Get PUEBI reference
    public function getReference($slug)
    {
        return $this->callFastAPI('GET', "/api/puebi/{$slug}");
    }
}