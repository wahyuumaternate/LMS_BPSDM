<?php

use Illuminate\Support\Facades\Route;
use Modules\Ujian\Http\Controllers\UjianController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('ujians', UjianController::class)->names('ujian');
});
