<?php

use Illuminate\Support\Facades\Route;
use Modules\Kelompok\Http\Controllers\KelompokController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('kelompoks', KelompokController::class)->names('kelompok');
});
