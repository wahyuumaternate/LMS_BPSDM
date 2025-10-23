<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminInstruktur\Http\Controllers\API\AuthController;
use Modules\AdminInstruktur\Http\Controllers\API\AdminInstrukturController;

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::post('admin/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('admin/logout', [AuthController::class, 'logout']);
        Route::get('admin/me', [AuthController::class, 'me']);
        Route::apiResource('admin', AdminInstrukturController::class);
    });
});
