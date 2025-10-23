<?php

use Illuminate\Support\Facades\Route;
use Modules\Evaluasi\Http\Controllers\EvaluasiController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('evaluasis', EvaluasiController::class)->names('evaluasi');
});
