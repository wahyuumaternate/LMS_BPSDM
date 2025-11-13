<?php

use Illuminate\Support\Facades\Route;
use Modules\Ujian\Http\Controllers\UjianController;
use Modules\Ujian\Http\Controllers\SoalUjianController;
use Modules\Ujian\Http\Controllers\HasilUjianController;

/*
|--------------------------------------------------------------------------
| Web Routes for Ujian Module
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin_instruktur'])
    ->prefix('content')
    ->group(function () {
        // Ujian resource routes
        Route::resource('ujians', UjianController::class);

        // Custom routes for SoalUjian
        // Route::get('soal-ujian/ujian/{ujianId}', [SoalUjianController::class, 'getByUjian'])->name('soal-ujian.by-ujian');
        Route::get('soal-ujian/validate-options/{id}', [SoalUjianController::class, 'validateOptions'])->name('soal-ujian.validate-options');
        Route::get('soal-ujian/bulk/create', [SoalUjianController::class, 'createBulk'])->name('soal-ujian.create-bulk');
        Route::post('soal-ujian/bulk/store', [SoalUjianController::class, 'storeBulk'])->name('soal-ujian.store-bulk');

        // Soal-Ujian routes with proper URLs
        Route::get('soal-ujian/ujian/{ujianId}', [SoalUjianController::class, 'getByUjian'])
            ->name('soal-ujian.by-ujian');
        Route::get('soal-ujian/create', [SoalUjianController::class, 'create'])
            ->name('soal-ujian.create');
        Route::post('soal-ujian', [SoalUjianController::class, 'store'])
            ->name('soal-ujian.store');
        Route::get('soal-ujian/{id}', [SoalUjianController::class, 'show'])
            ->name('soal-ujian.show');
        Route::get('soal-ujian/{id}/edit', [SoalUjianController::class, 'edit'])
            ->name('soal-ujian.edit');
        Route::put('soal-ujian/{id}', [SoalUjianController::class, 'update'])
            ->name('soal-ujian.update');
        Route::delete('soal-ujian/{id}', [SoalUjianController::class, 'destroy'])
            ->name('soal-ujian.destroy');

        // Routes for admin/instructors to try/simulate ujians
        Route::get('/ujians/{id}/simulate', [UjianController::class, 'simulateUjian'])->name('ujians.simulate');
        Route::post('/ujians/{id}/simulate', [UjianController::class, 'processSimulation'])->name('ujians.process-simulation');
        Route::get('/ujians/{id}/simulate-result', [UjianController::class, 'simulationResult'])->name('ujians.simulation-result');

        // HasilUjian routes
        Route::prefix('hasil-ujian')
            ->name('hasil-ujian.')
            ->group(function () {
                Route::get('/', [HasilUjianController::class, 'index'])->name('index');
                Route::get('/{id}', [HasilUjianController::class, 'show'])->name('show');
                Route::get('/peserta/{peserta_id}', [HasilUjianController::class, 'pesertaOverview'])->name('peserta-overview');
                Route::get('/ujian/{ujian_id}', [HasilUjianController::class, 'ujianOverview'])->name('ujian-overview');
            });
    });
