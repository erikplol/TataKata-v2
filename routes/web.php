<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\TextCheckerController;
use App\Http\Controllers\FastAPIController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/upload', [DocumentController::class, 'uploadForm'])->name('upload');
    Route::post('/upload', [DocumentController::class, 'upload'])->name('upload.post');
    Route::get('/download/{id}', [DocumentController::class, 'download'])->name('document.download');
    Route::get('/history', [HistoryController::class, 'index'])->name('history');
    Route::delete('/history/delete', [HistoryController::class, 'delete'])->name('history.delete');
    Route::delete('/history/bulk-delete', [HistoryController::class, 'bulkDelete'])->name('history.bulk-delete');
    Route::get('/history/bulk-download', [HistoryController::class, 'bulkDownload'])->name('history.bulk-download');
    Route::get('/correction/status/{document}', [DocumentController::class, 'showStatus'])->name('correction.status');
    Route::get('/correction/check-status/{document}', [DocumentController::class, 'checkStatus'])->name('correction.check-status');
    Route::get('/correction/{document}/original', [DocumentController::class, 'viewOriginal'])->name('correction.original');
    Route::post('/history/bulk-delete', [\App\Http\Controllers\HistoryController::class, 'bulkDelete'])->name('history.bulk-delete.post');
    Route::get('/correction/{document}', [DocumentController::class, 'showCorrection'])->name('correction.show');
});

require __DIR__.'/auth.php';