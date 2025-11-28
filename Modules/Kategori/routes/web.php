<?php

use Illuminate\Support\Facades\Route;
use Modules\Kategori\Http\Controllers\JenisKursusController;
use Modules\Kategori\Http\Controllers\KategoriKursusController;


// Route untuk KategoriKursus
Route::group(['prefix' => 'content'], function () {
    Route::resource('kategori-kursus', KategoriKursusController::class, ['as' => 'kategori']);

    // Perbaikan sintaks route untuk updateOrder (menggunakan ::class)
    Route::post('kategori-kursus/update-order', [KategoriKursusController::class, 'updateOrder'])
        ->name('kategori.kursus.updateOrder');

    // Perbaikan sintaks route untuk showBySlug (menggunakan ::class)
    Route::get('kategori/{slug}', [KategoriKursusController::class, 'showBySlug'])
        ->name('kategori.kursus.showBySlug');

         // Route untuk JenisKursus
    Route::resource('jenis-kursus', JenisKursusController::class, ['as' => 'kategori']);
    
    Route::post('jenis-kursus/update-order', [JenisKursusController::class, 'updateOrder'])
        ->name('kategori.jenis-kursus.updateOrder');
});
