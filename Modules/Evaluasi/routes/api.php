<?php

use Illuminate\Support\Facades\Route;
use Modules\Evaluasi\Http\Controllers\EvaluasiController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('evaluasis', EvaluasiController::class)->names('evaluasi');
});
