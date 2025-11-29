<?php

use Illuminate\Support\Facades\Route;
use Modules\Sertifikat\Http\Controllers\SertifikatController;
use Modules\Sertifikat\Http\Controllers\SertifikatVerificationController;
use Modules\Sertifikat\Http\Controllers\TemplateSertifikatController;

// Routes untuk Template Sertifikat
Route::group(['prefix' => 'certificates/admin'], function () {
    // Templates listing
    Route::get('templates', [TemplateSertifikatController::class, 'index'])->name('template.sertifikat.index');

    // Create template
    Route::get('templates/create', [TemplateSertifikatController::class, 'create'])->name('template.sertifikat.create');
    Route::post('templates', [TemplateSertifikatController::class, 'store'])->name('template.sertifikat.store');

    // View, edit, update, delete template
    Route::get('templates/{id}', [TemplateSertifikatController::class, 'show'])->name('template.sertifikat.show');
    Route::get('templates/{id}/edit', [TemplateSertifikatController::class, 'edit'])->name('template.sertifikat.edit');
    Route::put('templates/{id}', [TemplateSertifikatController::class, 'update'])->name('template.sertifikat.update');
    Route::delete('templates/{id}', [TemplateSertifikatController::class, 'destroy'])->name('template.sertifikat.destroy');

    // Preview dan download template dalam format PDF
    Route::get('templates/{id}/preview', [TemplateSertifikatController::class, 'preview'])->name('template.sertifikat.preview');
    Route::get('templates/{id}/preview-pdf', [TemplateSertifikatController::class, 'previewPdf'])->name('template.sertifikat.preview.pdf');
    Route::get('templates/{id}/download-pdf', [TemplateSertifikatController::class, 'downloadPdf'])->name('template.sertifikat.download.pdf');

   
});

Route::middleware(['auth:admin_instruktur'])->group(function() {
    // ... existing routes ...
    
    // AJAX endpoint
    Route::get('/sertifikat/get-peserta-by-kursus/{kursusId}', 
        [SertifikatController::class, 'getPesertaByKursus'])
        ->name('sertifikat.get-peserta-by-kursus');
    
    
    
    // ... other routes ...
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::prefix('sertifikat')->group(function() {
        Route::get('/', [SertifikatController::class, 'index'])->name('sertifikat.index');
        Route::get('/create', [SertifikatController::class, 'create'])->name('sertifikat.create');
        Route::post('/', [SertifikatController::class, 'store'])->name('sertifikat.store');
        Route::get('/{id}', [SertifikatController::class, 'show'])->name('sertifikat.show');
        Route::get('/{id}/edit', [SertifikatController::class, 'edit'])->name('sertifikat.edit');
        Route::put('/{id}', [SertifikatController::class, 'update'])->name('sertifikat.update');
        Route::delete('/{id}', [SertifikatController::class, 'destroy'])->name('sertifikat.destroy');
        
        // Preview & Download
        Route::get('/{id}/preview', [SertifikatController::class, 'preview'])->name('sertifikat.preview');
        Route::get('/{id}/download', [SertifikatController::class, 'download'])->name('sertifikat.download');
        
        // Generate PDF
        Route::post('/{id}/generate-pdf', [SertifikatController::class, 'generatePdf'])->name('sertifikat.generate-pdf');
        
        // Email
        Route::post('/{id}/send-email', [SertifikatController::class, 'sendEmail'])->name('sertifikat.send-email');
        
        // Bulk Actions
        Route::get('/bulk/generate-form', [SertifikatController::class, 'bulkGenerateForm'])->name('sertifikat.bulk.generate-form');
        Route::post('/bulk/generate', [SertifikatController::class, 'bulkGenerate'])->name('sertifikat.bulk.generate');
        
        // Revoke/Restore
        Route::post('/{id}/revoke', [SertifikatController::class, 'revoke'])->name('sertifikat.revoke');
        Route::post('/{id}/restore', [SertifikatController::class, 'restore'])->name('sertifikat.restore');
    });
});

// Public Verification (no auth required)
Route::get('/verify-certificate/{nomor}', [SertifikatVerificationController::class, 'verify'])
    ->name('sertifikat.verify');
Route::post('/verify-certificate/search', [SertifikatVerificationController::class, 'search'])
    ->name('sertifikat.verify.search');