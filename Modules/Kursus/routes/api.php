<?php

use Illuminate\Support\Facades\Route;
use Modules\Kursus\Http\Controllers\KursusController;
use Modules\Kursus\Http\Controllers\PendaftaranController;

Route::middleware('auth:sanctum')->group(function () {
    // Kursus Routes
    Route::get('kursus', [KursusController::class, 'index']);
    Route::post('kursus', [KursusController::class, 'store'])
        ->middleware('ability:super_admin,instruktur');
    Route::get('kursus/{id}', [KursusController::class, 'show']);
    Route::put('kursus/{id}', [KursusController::class, 'update'])
        ->middleware('ability:super_admin,instruktur');
    Route::delete('kursus/{id}', [KursusController::class, 'destroy'])
        ->middleware('ability:super_admin,instruktur');
    // Pendaftaran Routes
    Route::get('pendaftaran', [PendaftaranController::class, 'index']);
    Route::post('kursus/{id}/daftar', [PendaftaranController::class, 'store'])
        ->middleware('ability:peserta');
    Route::put('pendaftaran/{id}/status', [PendaftaranController::class, 'updateStatus'])
        ->middleware('ability:super_admin,instruktur');
    Route::get('pendaftaran/peserta', [PendaftaranController::class, 'getByPeserta'])
        ->middleware('ability:peserta');
    Route::get('pendaftaran/kursus/{kursusId}', [PendaftaranController::class, 'getByKursus'])
        ->middleware('ability:super_admin,instruktur');
});
