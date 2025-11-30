<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\BeritaController;
use Modules\Blog\Http\Controllers\KategoriBeritaController;

/*
|--------------------------------------------------------------------------
| Blog Routes
|--------------------------------------------------------------------------
| File: Modules/Blog/Routes/web.php
*/

Route::middleware(['auth:admin_instruktur'])->prefix('admin')->group(function () {
    
    // Berita Routes
    Route::resource('berita', BeritaController::class);
    
    // Extra Berita Actions
    Route::post('berita/{berita}/publish', [BeritaController::class, 'publish'])->name('berita.publish');
    Route::post('berita/{berita}/archive', [BeritaController::class, 'archive'])->name('berita.archive');
    
    // Kategori Berita Routes
    Route::resource('kategori-berita', KategoriBeritaController::class)->except(['show']);
});