<?php

use Illuminate\Support\Facades\Route;
use Modules\Materi\Http\Controllers\MateriController;

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