<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;
use Illuminate\View\View;
use Illuminate\Support\Collection;

class HistoryController extends Controller
{
    protected $middleware = ['auth'];

    public function index(): View
    {
        $userId = Auth::id();
        $documents = Document::where('user_id', $userId)
                             ->orderBy('created_at', 'desc')
                             ->get()
                             ->map(function ($doc) {
                                 return [
                                     'id' => $doc->id,
                                     'type' => 'document',
                                     'created_at' => $doc->created_at,
                                     'name' => $doc->file_name,
                                     'upload_status' => $doc->upload_status,
                                     'file_location' => $doc->file_location,
                                 ];
                             });

        return view('history', [
            'history' => $documents,
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'item_type' => 'required|in:document',
        ]);

        $itemId = $request->input('item_id');
        $itemType = $request->input('item_type');
        $userId = Auth::id();

        try {
            if ($itemType === 'document') {
                $item = Document::where('id', $itemId)
                                ->where('user_id', $userId)
                                ->firstOrFail();
                
                if (!empty($item->file_location) && Storage::disk('public')->exists($item->file_location)) {
                    Storage::disk('public')->delete($item->file_location);
                }

                $item->delete();
                $message = 'Dokumen berhasil dihapus dari riwayat.';

            } else {
                abort(400, 'Tipe item tidak valid.');
            }

            return redirect()->route('history')->with('success', $message);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('history')->with('error', 'Item tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.');
        } catch (\Exception $e) {
            \Log::error("Deletion Error: " . $e->getMessage());
            return redirect()->route('history')->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'selected_items' => 'required|array',
            'selected_items.*' => 'string',
        ]);

        $userId = Auth::id();
        $deletedCount = 0;
        $errors = [];

        foreach ($request->input('selected_items') as $itemKey) {
            $parts = explode('_', $itemKey, 2);
            if (count($parts) !== 2) {
                $errors[] = "Format item tidak valid: {$itemKey}";
                continue;
            }

            [$itemType, $itemId] = $parts;

            try {
                if (!is_numeric($itemId)) {
                    throw new \InvalidArgumentException('ID item tidak valid');
                }

                $item = Document::where('id', $itemId)->where('user_id', $userId)->firstOrFail();
                if (!empty($item->file_location) && Storage::disk('public')->exists($item->file_location)) {
                    Storage::disk('public')->delete($item->file_location);
                }
                $item->delete();
                $deletedCount++;
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $errors[] = "Item {$itemType} ID {$itemId} tidak ditemukan atau tidak memiliki izin.";
            } catch (\Exception $e) {
                \Log::error("Bulk Deletion Error for {$itemKey}: " . $e->getMessage());
                $errors[] = "Kesalahan saat menghapus item {$itemType} ID {$itemId}.";
            }
        }

        $message = "{$deletedCount} item berhasil dihapus.";
        if (!empty($errors)) {
            $message .= " Namun, terjadi kesalahan pada beberapa item.";
        }

        return redirect()->route('history')->with('success', $message);
    }

    public function bulkDownload(Request $request)
    {
        $request->validate([
            'selected_items' => 'required|array',
            'selected_items.*' => 'string', 
        ]);

        $userId = Auth::id();
        $zipFileName = 'takatakata_bulk_download_' . time() . '.zip';
        $tempPath = storage_path('app/public/' . $zipFileName);
        
        $zip = new \ZipArchive();

        if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            return redirect()->route('history')->with('error', 'Gagal membuat file ZIP.');
        }

        $downloadedCount = 0;
        
        foreach ($request->input('selected_items') as $itemKey) {
            [$itemType, $itemId] = explode('_', $itemKey);

            if ($itemType === 'document') {
                $document = Document::where('id', $itemId)
                                    ->where('user_id', $userId)
                                    ->where('upload_status', 'Completed')
                                    ->first();

                if ($document) {
                    $filePath = Storage::disk('public')->path($document->file_location);
                    $fileName = $document->file_name . '_' . $document->id . '.pdf';

                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, $fileName);
                        $downloadedCount++;
                    }
                }
            }
        }

        $zip->close();

        if ($downloadedCount === 0) {
            return redirect()->route('history')->with('error', 'Tidak ada dokumen yang valid atau selesai untuk diunduh.');
        }

        return response()->download($tempPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
