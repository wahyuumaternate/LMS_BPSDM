<?php

use Illuminate\Support\Facades\Route;
use Modules\Modul\Http\Controllers\API\ModulController;

Route::prefix('v1')->group(function () {
    // Public Routes (no authentication required)
    Route::get('modul/{id}/no-auth', [ModulController::class, 'showNoAuth'])->name('modul.show.noAuth');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('modul/reorder', [ModulController::class, 'reorder']);
        Route::apiResource('modul', ModulController::class);
    });
});
