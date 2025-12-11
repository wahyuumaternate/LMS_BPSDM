<?php

use Illuminate\Support\Facades\Route;
use Modules\Ujian\Http\Controllers\API\UjianController;

/*
|--------------------------------------------------------------------------
| API Routes - Ujian Peserta
|--------------------------------------------------------------------------
|
| Routes untuk peserta mengerjakan ujian
| Semua route memerlukan autentikasi (sanctum middleware)
|
*/

Route::group(
    [
        'prefix' => 'v1/ujian',
        'middleware' => ['auth:sanctum'],
    ],
    function () {
        // Daftar ujian yang tersedia
        Route::get('/', [UjianController::class, 'index']);

        // Detail ujian
        Route::get('/{id}', [UjianController::class, 'show']);

        // Status ujian (apakah sudah dikerjakan atau belum)
        Route::get('/{id}/status', [UjianController::class, 'status']);

        // Mulai mengerjakan ujian
        Route::post('/{id}/mulai', [UjianController::class, 'mulaiUjian']);

        // Submit jawaban ujian
        Route::post('/{id}/submit', [UjianController::class, 'submitUjian']);

        // Lihat hasil ujian
        Route::get('/{id}/hasil', [UjianController::class, 'hasil']);

        // Semua hasil ujian peserta
        Route::get('/my-results', [UjianController::class, 'myResults']);
    },
);
