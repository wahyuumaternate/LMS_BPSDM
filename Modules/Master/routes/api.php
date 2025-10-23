<?php

use Illuminate\Support\Facades\Route;
use Modules\Master\Http\Controllers\OPDController;
use Modules\Master\Http\Controllers\KategoriKursusController;


Route::middleware('auth:sanctum')->group(function () {
    // OPD Routes
    Route::get('opd', [OPDController::class, 'index']);
    Route::post('opd', [OPDController::class, 'store'])->middleware('ability:super_admin');
    Route::get('opd/{id}', [OPDController::class, 'show']);
    Route::put('opd/{id}', [OPDController::class, 'update'])->middleware('ability:super_admin');
    Route::delete('opd/{id}', [OPDController::class, 'destroy'])->middleware('ability:super_admin');
    // Kategori Kursus Routes
    Route::get('kategori-kursus', [KategoriKursusController::class, 'index']);
    Route::post('kategori-kursus', [KategoriKursusController::class, 'store'])->middleware('ability:super_admin');
    Route::get('kategori-kursus/{id}', [KategoriKursusController::class, 'show']);
    Route::put('kategori-kursus/{id}', [KategoriKursusController::class, 'update'])->middleware('ability:super_admin');
    Route::delete('kategori-kursus/{id}', [KategoriKursusController::class, 'destroy'])->middleware('ability:super_admin');
});
