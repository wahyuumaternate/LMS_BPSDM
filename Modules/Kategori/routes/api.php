<?php

use Illuminate\Support\Facades\Route;
use Modules\Kategori\Http\Controllers\API\KategoriKursusController;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('kategori', KategoriKursusController::class);
    });
});
