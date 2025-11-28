<?php

use Illuminate\Support\Facades\Route;
use Modules\Kategori\Http\Controllers\API\JenisKursusController;
use Modules\Kategori\Http\Controllers\API\KategoriKursusController;

Route::prefix('v1')->group(function () {
  // Public routes - tidak perlu authentication
    Route::get('kategori-kursus', [KategoriKursusController::class, 'index']);
    Route::get('kategori-kursus/{id}', [KategoriKursusController::class, 'show']);
    
    Route::get('jenis-kursus', [JenisKursusController::class, 'index']);
    Route::get('jenis-kursus/{id}', [JenisKursusController::class, 'show']);
});
