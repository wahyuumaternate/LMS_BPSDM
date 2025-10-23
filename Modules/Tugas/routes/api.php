<?php

use Illuminate\Support\Facades\Route;
use Modules\Tugas\Http\Controllers\TugasController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('tugas', TugasController::class)->names('tugas');
});
