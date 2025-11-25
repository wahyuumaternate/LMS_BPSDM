<?php

use Illuminate\Support\Facades\Route;
use Modules\SesiKehadiran\Http\Controllers\API\SesiKehadiranController;
use Modules\SesiKehadiran\Http\Controllers\API\KehadiranController;

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
   
   // =====================================================
    // STUDENT (PESERTA) ROUTES - Khusus untuk peserta
    // =====================================================
    Route::prefix('student')->middleware('auth:peserta')->group(function () {
        
        // Kehadiran Routes untuk Peserta
        Route::get('kehadiran', [KehadiranController::class, 'index']);
        Route::get('kehadiran/report', [KehadiranController::class, 'report']);
        Route::get('kehadiran/available-sessions', [KehadiranController::class, 'availableSessions']);
        Route::get('kehadiran/kursus/{kursus_id}', [KehadiranController::class, 'getByKursus']);
        Route::get('kehadiran/sesi/{sesi_id}/status', [KehadiranController::class, 'checkStatus']);
        Route::get('kehadiran/{id}', [KehadiranController::class, 'show']);
        
        // Check-in, Check-out & Izin/Sakit
        Route::post('kehadiran/checkin', [KehadiranController::class, 'checkin']);
        Route::post('kehadiran/checkout', [KehadiranController::class, 'checkout']);
        Route::post('kehadiran/izin', [KehadiranController::class, 'submitIzin']);
    });
});
