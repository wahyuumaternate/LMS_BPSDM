<?php

use Illuminate\Support\Facades\Route;
use Modules\Sertifikat\Http\Controllers\SertifikatController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('sertifikats', SertifikatController::class)->names('sertifikat');
});
