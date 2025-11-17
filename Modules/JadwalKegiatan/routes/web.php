<?php

use Illuminate\Support\Facades\Route;
use Modules\JadwalKegiatan\Http\Controllers\JadwalKegiatanController;

// Routes untuk Jadwal Kegiatan
Route::post('jadwal-kegiatan', [JadwalKegiatanController::class, 'store'])->name('jadwal.store');
Route::patch('jadwal-kegiatan/{id}', [JadwalKegiatanController::class, 'update'])->name('jadwal.update');
Route::delete('jadwal-kegiatan/{id}', [JadwalKegiatanController::class, 'destroy'])->name('jadwal.destroy');
