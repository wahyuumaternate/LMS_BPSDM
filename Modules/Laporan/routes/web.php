<?php

use Illuminate\Support\Facades\Route;
use Modules\Laporan\Http\Controllers\LaporanController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('laporans', LaporanController::class)->names('laporan');
});
