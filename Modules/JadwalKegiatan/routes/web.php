<?php

use Illuminate\Support\Facades\Route;
use Modules\JadwalKegiatan\Http\Controllers\JadwalKegiatanController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('jadwalkegiatans', JadwalKegiatanController::class)->names('jadwalkegiatan');
});
