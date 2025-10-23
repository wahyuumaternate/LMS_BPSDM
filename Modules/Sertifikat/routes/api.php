<?php

use Illuminate\Support\Facades\Route;
use Modules\Sertifikat\Http\Controllers\SertifikatController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('sertifikats', SertifikatController::class)->names('sertifikat');
});
