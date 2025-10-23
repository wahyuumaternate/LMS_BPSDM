<?php

use Illuminate\Support\Facades\Route;
use Modules\Kelompok\Http\Controllers\KelompokController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('kelompoks', KelompokController::class)->names('kelompok');
});
