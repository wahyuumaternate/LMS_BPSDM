<?php

use Illuminate\Support\Facades\Route;
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
