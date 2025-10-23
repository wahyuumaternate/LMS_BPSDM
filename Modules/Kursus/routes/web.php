<?php

use Illuminate\Support\Facades\Route;
use Modules\Kursus\Http\Controllers\KursusController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('kursuses', KursusController::class)->names('kursus');
});
