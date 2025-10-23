<?php

use Illuminate\Support\Facades\Route;
use Modules\OPD\Http\Controllers\API\OPDController;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('opd', OPDController::class);
    });
});
