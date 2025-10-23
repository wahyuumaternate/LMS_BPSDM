<?php

use Illuminate\Support\Facades\Route;
use Modules\Kursus\Http\Controllers\API\KursusController;
use Modules\Kursus\Http\Controllers\API\PrasyaratController;
use Modules\Kursus\Http\Controllers\API\PendaftaranKursusController;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('kursus', KursusController::class);
        Route::apiResource('prasyarat', PrasyaratController::class);
        Route::apiResource('pendaftaran', PendaftaranKursusController::class);
    });
});
