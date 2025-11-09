<?php

use Illuminate\Support\Facades\Route;
use Modules\Kursus\Http\Controllers\API\KursusController;
use Modules\Kursus\Http\Controllers\API\KursusPublicController;
use Modules\Kursus\Http\Controllers\API\PrasyaratController;
use Modules\Kursus\Http\Controllers\API\PendaftaranKursusController;

Route::prefix('v1')->group(function () {
    // Public Routes (no authentication required)
    Route::get('kursus', [KursusController::class, 'index']);
    Route::get('kursus/{id}', [KursusController::class, 'show']);

    // Protected Routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {
        // Admin/Staff Kursus management
        Route::apiResource('kursus-management', KursusController::class)->names([
            'index' => 'kursus.management.index',
            'store' => 'kursus.management.store',
            'show' => 'kursus.management.show',
            'update' => 'kursus.management.update',
            'destroy' => 'kursus.management.destroy',
        ]);

        // Other protected endpoints
        Route::apiResource('prasyarat', PrasyaratController::class);
        Route::apiResource('pendaftaran', PendaftaranKursusController::class);
    });
});
