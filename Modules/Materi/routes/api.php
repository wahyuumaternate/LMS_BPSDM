<?php

use Illuminate\Support\Facades\Route;
use Modules\Materi\Http\Controllers\API\MateriController;
use Modules\Materi\Http\Controllers\API\ProgresMateriController;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('materi', MateriController::class);
        Route::post('materi/reorder', [MateriController::class, 'reorder']);

        Route::apiResource('progres-materi', ProgresMateriController::class);
        Route::post('progres-materi/update-progress', [ProgresMateriController::class, 'updateProgress']);
    });
});
