<?php

use Illuminate\Support\Facades\Route;
use Modules\OPD\Http\Controllers\API\OPDController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('opds', OPDController::class)->names('opd');
});
