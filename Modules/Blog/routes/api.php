<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\API\BeritaController;
use Modules\Blog\Http\Controllers\API\KategoriBeritaController;

/*
|--------------------------------------------------------------------------
| Blog API Routes
|--------------------------------------------------------------------------
| File: Modules/Blog/Routes/api.php
*/

Route::prefix('v1')->group(function () {
    
    // Berita Routes
    Route::prefix('berita')->group(function () {
        Route::get('/', [BeritaController::class, 'index'])->name('api.berita.index');
        Route::get('/latest', [BeritaController::class, 'latest'])->name('api.berita.latest');
        Route::get('/popular', [BeritaController::class, 'popular'])->name('api.berita.popular');
        Route::get('/slug/{slug}', [BeritaController::class, 'showBySlug'])->name('api.berita.showBySlug');
        Route::get('/{id}', [BeritaController::class, 'show'])->name('api.berita.show');
    });

    // Kategori Berita Routes
    Route::prefix('kategori-berita')->group(function () {
        Route::get('/', [KategoriBeritaController::class, 'index'])->name('api.kategori-berita.index');
        Route::get('/slug/{slug}', [KategoriBeritaController::class, 'showBySlug'])->name('api.kategori-berita.showBySlug');
        Route::get('/{id}', [KategoriBeritaController::class, 'show'])->name('api.kategori-berita.show');
        Route::get('/{id}/berita', [KategoriBeritaController::class, 'berita'])->name('api.kategori-berita.berita');
    });
});