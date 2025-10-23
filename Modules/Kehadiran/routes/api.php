<?php

use Illuminate\Support\Facades\Route;
use Modules\Kehadiran\Http\Controllers\KehadiranController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('kehadirans', KehadiranController::class)->names('kehadiran');
});
