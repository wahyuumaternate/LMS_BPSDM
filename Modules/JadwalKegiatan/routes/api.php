<?php

use Illuminate\Support\Facades\Route;
use Modules\JadwalKegiatan\Http\Controllers\API\JadwalKegiatanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Jadwal Kegiatan Routes
    Route::get('jadwal-kegiatan', [JadwalKegiatanController::class, 'index']);
    Route::post('jadwal-kegiatan', [JadwalKegiatanController::class, 'store']);
    Route::get('jadwal-kegiatan/{id}', [JadwalKegiatanController::class, 'show']);
    Route::put('jadwal-kegiatan/{id}', [JadwalKegiatanController::class, 'update']);
    Route::delete('jadwal-kegiatan/{id}', [JadwalKegiatanController::class, 'destroy']);

    // Jadwal Kegiatan berdasarkan kursus
    Route::get('kursus/{kursus_id}/jadwal-kegiatan', [JadwalKegiatanController::class, 'getByKursus']);

    // Jadwal Kegiatan upcoming & today
    Route::get('jadwal-kegiatan/upcoming', [JadwalKegiatanController::class, 'getUpcoming']);
    Route::get('jadwal-kegiatan/today', [JadwalKegiatanController::class, 'getToday']);
});
