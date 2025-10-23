<?php

use Illuminate\Support\Facades\Route;
use Modules\Peserta\Http\Controllers\API\AuthController;
use Modules\Peserta\Http\Controllers\API\PesertaController;

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::post('peserta/register', [AuthController::class, 'register']);
    Route::post('peserta/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('peserta/logout', [AuthController::class, 'logout']);
        Route::get('peserta/me', [AuthController::class, 'me']);
        Route::apiResource('peserta', PesertaController::class);
    });
});
