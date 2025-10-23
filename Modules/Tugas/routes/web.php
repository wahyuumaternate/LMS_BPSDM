<?php

use Illuminate\Support\Facades\Route;
use Modules\Tugas\Http\Controllers\TugasController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('tugas', TugasController::class)->names('tugas');
});
