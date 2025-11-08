<?php

use Illuminate\Support\Facades\Route;
use Modules\Materi\Http\Controllers\MateriController;
use Modules\Materi\Http\Controllers\ProgresMateriController;

/*
|--------------------------------------------------------------------------
| Web Routes for Materi Module
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin_instruktur'])->prefix('admin')->group(function () {
    // Resource routes
    Route::resource('materi', MateriController::class);
    
    // Additional routes
    Route::get('materi/reorder', [MateriController::class, 'reorderForm'])->name('materi.reorder.form');
    Route::post('materi/reorder', [MateriController::class, 'reorderUpdate'])->name('materi.reorder.update');
    Route::post('materi/{id}/toggle-publish', [MateriController::class, 'togglePublish'])->name('materi.toggle-publish');
});


/*
|--------------------------------------------------------------------------
| Web Routes for Progress Materi Module
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin_instruktur'])->group(function () {
    // Dashboard overview
    Route::get('content/progress-dashboard', [ProgresMateriController::class, 'dashboard'])->name('progres-materi.dashboard');
    
    // Resource routes
    Route::resource('content/progress', ProgresMateriController::class)->names([
        'index' => 'progres-materi.index',
        'create' => 'progres-materi.create',
        'store' => 'progres-materi.store',
        'show' => 'progres-materi.show',
        'edit' => 'progres-materi.edit',
        'update' => 'progres-materi.update',
        'destroy' => 'progres-materi.destroy',
    ]);
    
    // Overview routes
    Route::get('content/peserta/{id}/progress', [ProgresMateriController::class, 'pesertaOverview'])->name('progres-materi.peserta-overview');
    Route::get('content/materials/{id}/progress', [ProgresMateriController::class, 'materiOverview'])->name('progres-materi.materi-overview');
});