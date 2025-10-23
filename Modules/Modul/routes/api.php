<?php

use Illuminate\Support\Facades\Route;
use Modules\Modul\Http\Controllers\API\ModulController;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('modul', ModulController::class);
        Route::post('modul/reorder', [ModulController::class, 'reorder']);
    });
});
