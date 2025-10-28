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
        try {
            $file = $request->file('file');
            $document_name = $request->input('document_name');

            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.pdf';
            // Use the configured default filesystem disk so switching to S3 is possible
            $usedDisk = config('filesystems.default') ?: 'public';
            $path = $file->storeAs('documents', $filename, $usedDisk);

            // Debug info: record which DB driver and filesystem disk are in use, and where the file landed
            \Log::info('Upload: stored file', [
                'file_path' => $path,
                'disk' => $usedDisk,
                'db_driver' => config('database.default'),
                'user_id' => Auth::id(),
            ]);

            $document = Document::create([
                'user_id' => Auth::id(),
                'file_name' => $document_name,
                'file_location' => $path,
                'disk' => $usedDisk,
                'upload_status' => 'Processing', 
            ]);

            \Log::info('Upload: created Document record', [
                'document_id' => $document->id,
                'file_location' => $document->file_location,
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
        } catch (\Throwable $e) {
            \Log::error('Upload failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan saat mengunggah dokumen. Silakan coba lagi.');
        }
    }

    public function checkStatus($id)
    {
        try {
            \Log::info("🔵 Polling received for Document ID {$id}"); 

            $document = Document::find($id);
            if (! $document) {
                return response()->json([
                    'status' => 'Deleted',
                    'done' => true,
                    'details' => 'Dokumen telah dihapus oleh pengguna.',
                    'progress' => [],
                    'redirect_url' => null
                ]);
            }

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
                'progress' => array_slice($document->progress_log ?? [], -20),
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

    /**
     * Stream or redirect to the original uploaded PDF so users can view it while processing.
     */
    public function viewOriginal($id)
    {
        $document = Document::findOrFail($id);

        // Allow access if the request carries a valid signed URL OR the
        // authenticated user owns the document OR the request includes a
        // worker token header. The worker token is an additional layer used
        // when the worker fetches the signed URL; it helps mitigate risks if
        // a signed URL is leaked (the header must also be present).
        $hasValidSignature = request()->hasValidSignature();
        $workerHeader = request()->header('X-Worker-Token');
        $expectedWorkerToken = env('WORKER_TOKEN');
        $hasValidWorkerToken = !empty($expectedWorkerToken) && is_string($workerHeader) && hash_equals((string) $expectedWorkerToken, (string) $workerHeader);
        $isOwner = Auth::check() && $document->user_id === Auth::id();

        \Log::info('viewOriginal access attempt', [
            'document_id' => $document->id,
            'has_valid_signature' => $hasValidSignature,
            'has_worker_header' => request()->hasHeader('X-Worker-Token'),
            'has_valid_worker_token' => $hasValidWorkerToken,
            'is_authenticated' => Auth::check(),
            'is_owner' => $isOwner,
            'request_url' => request()->fullUrl(),
            'has_signature_param' => request()->has('signature'),
            'has_expires_param' => request()->has('expires'),
            'signature_value' => request()->get('signature'),
            'expires_value' => request()->get('expires'),
            'current_time' => time()
        ]);

        if (! $hasValidSignature && ! $hasValidWorkerToken) {
            if (! $isOwner) {
                \Log::warning('viewOriginal access denied - no valid signature, no worker token, and not owner', [
                    'document_id' => $document->id,
                    'url' => request()->fullUrl()
                ]);
                abort(403, 'Anda tidak memiliki akses ke file ini.');
            }
        }

        $path = $document->file_location;
        if (empty($path)) {
            abort(404, 'File tidak ditemukan.');
        }

    // Prefer the disk recorded on the Document (if present) so files stored on
    // S3/MinIO or other remote disks are served correctly. Fall back to the
    // configured default or 'public' for legacy records.
    $diskName = $document->disk ?: config('filesystems.default') ?: 'public';

        try {
            $disk = \Storage::disk($diskName);

            // For local drivers we can return a local file path
            if (method_exists($disk, 'path')) {
                $localPath = $disk->path($path);
                if (file_exists($localPath)) {
                    return response()->file($localPath, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . basename($localPath) . '"'
                    ]);
                }
            }

            if (method_exists($disk, 'temporaryUrl')) {
                try {
                    $url = $disk->temporaryUrl($path, now()->addMinutes(15));
                    return redirect()->away($url);
                } catch (\BadMethodCallException $e) {
                    \Log::info('Disk does not support temporaryUrl; falling back to stream', ['document_id' => $document->id, 'disk' => $diskName, 'message' => $e->getMessage()]);
                } catch (\Exception $e) {
                    \Log::warning('temporaryUrl failed for Document ID ' . $document->id . ': ' . $e->getMessage(), ['document_id' => $document->id]);
                }
            }

            // Fallback: stream the file through the app
            $stream = $disk->readStream($path);
            if ($stream === false) {
                abort(404, 'File tidak dapat diakses.');
            }

            return response()->stream(function () use ($stream) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . ($document->file_name ?: 'document') . '.pdf"'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error serving original file', ['document_id' => $document->id, 'error' => $e->getMessage()]);
            abort(500, 'Terjadi kesalahan saat mengakses file.');
        }
    }
}
