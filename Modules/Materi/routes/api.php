<?php

use Illuminate\Support\Facades\Route;
use Modules\Materi\Http\Controllers\MateriController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('materis', MateriController::class)->names('materi');
});
