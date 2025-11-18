<?php

use Illuminate\Support\Facades\Route;
use Modules\SesiKehadiran\Http\Controllers\SesiKehadiranController;

/*
|--------------------------------------------------------------------------
| Web Routes - Sesi Kehadiran
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Routes untuk Sesi Kehadiran
    Route::prefix('kursus/{kursusId}/sesi-kehadiran')->group(function () {
        Route::get('/', [SesiKehadiranController::class, 'index'])
            ->name('sesi-kehadiran.index');
    });

    Route::prefix('sesi-kehadiran')->name('sesi-kehadiran.')->group(function () {
        Route::post('/', [SesiKehadiranController::class, 'store'])
            ->name('store');
        Route::patch('/{id}', [SesiKehadiranController::class, 'update'])
            ->name('update');
        Route::delete('/{id}', [SesiKehadiranController::class, 'destroy'])
            ->name('destroy');
        Route::get('/{id}/detail', [SesiKehadiranController::class, 'detail'])
            ->name('detail');
    });

    // Routes untuk Kehadiran
    Route::prefix('kehadiran')->name('kehadiran.')->group(function () {
        Route::patch('/{id}/update-status', [SesiKehadiranController::class, 'updateStatusKehadiran'])
            ->name('update-status');
    });
});
