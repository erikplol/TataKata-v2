<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\History;
use App\Jobs\ProcessDocumentCorrection; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function uploadForm()
    {
        return view('upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'document_name' => 'required|string',
            'file' => 'required|mimes:pdf|max:10240',
        ]);

        $file = $request->file('file');
        $document_name = $request->input('document_name');

        $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.pdf';
        $path = $file->storeAs('documents', $filename, 'public');

        $document = Document::create([
            'user_id' => Auth::id(),
            'file_name' => $document_name,
            'file_location' => $path,
            'upload_status' => 'Processing', 
        ]);

        History::create([
            'user_id' => Auth::id(),
            'document_id' => $document->id,
            'activity_type' => 'upload',
            'details' => 'Dokumen diunggah oleh user',
        ]);

        ProcessDocumentCorrection::dispatch($document);

        return redirect()->route('correction.status', $document->id) 
                         ->with('success', 'Dokumen berhasil diunggah dan sedang diproses...');
    }

    public function checkStatus($id)
    {
        try {
            \Log::info("🔵 Polling received for Document ID {$id}"); 

            $document = Document::findOrFail($id);

            if ($document->user_id !== Auth::id()) {
                return response()->json(['status' => 'Unauthorized'], 403);
            }

            $document->refresh();
            $status = trim($document->upload_status ?? '');
            $isCompleted = ($status === 'Completed');

            \Log::info("🟢 Document ID {$id} status: '{$status}'. Done: {$isCompleted}");

            return response()->json([
                'status' => $document->upload_status,
                'done' => $isCompleted,
                'details' => $document->details,
                'redirect_url' => route('correction.show', $document->id)
            ]);
        } catch (\Throwable $e) {
            \Log::error("❌ checkStatus ERROR: " . $e->getMessage(), [
                'document_id' => $id
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function showStatus($id)
    {
        $document = Document::findOrFail($id); 
        
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        return view('correction_status', compact('document'));
    }

    public function showCorrection($id)
    {
        DB::reconnect();
        $document = Document::findOrFail($id);
        $document->refresh();

        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        $statusLower = strtolower(trim($document->upload_status ?? ''));
        $isCompleted = ($statusLower === 'completed');

        if (!$isCompleted) {
            \Log::warning("⚠️ Clash Detected: User tried accessing completed page for ID {$id} but status is '{$document->upload_status}'");
            return view('correction_status', compact('document'));
        }

        return view('correction', [
            'document' => $document,
            'original_text' => $document->original_text,
            'corrected_text' => $document->corrected_text,
        ]);
    }

    public function download($id)
    {
        $document = Document::findOrFail($id);

        if ($document->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke koreksi ini.');
        }

        $correctedText = $document->corrected_text;
        
        // Cek apakah hasil koreksi tersedia
        if (empty($correctedText)) {
            return back()->with('error', 'Hasil koreksi belum tersedia.');
        }

        // Membuat nama file yang aman dan deskriptif
        $safeFileName = Str::slug($document->file_name);
        $timestamp = now()->format('Ymd-His');
        $filename = "koreksi-{$safeFileName}-{$timestamp}.md";

        // Mengirimkan konten sebagai response dengan tipe text/markdown
        return response($correctedText, 200)
            ->header('Content-Type', 'text/markdown')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
