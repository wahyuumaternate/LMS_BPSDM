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
    // Sesi Kehadiran Routes
    Route::get('sesi-kehadiran', [SesiKehadiranController::class, 'index']);
    Route::post('sesi-kehadiran', [SesiKehadiranController::class, 'store']);
    Route::get('sesi-kehadiran/{id}', [SesiKehadiranController::class, 'show']);
    Route::put('sesi-kehadiran/{id}', [SesiKehadiranController::class, 'update']);
    Route::delete('sesi-kehadiran/{id}', [SesiKehadiranController::class, 'destroy']);
    Route::get('sesi-kehadiran/{id}/qrcode', [SesiKehadiranController::class, 'getQrCode']);

    // Kehadiran Routes
    Route::get('kehadiran', [KehadiranController::class, 'index']);
    Route::post('kehadiran', [KehadiranController::class, 'store']);
    Route::get('kehadiran/{id}', [KehadiranController::class, 'show']);
    Route::put('kehadiran/{id}', [KehadiranController::class, 'update']);
    Route::delete('kehadiran/{id}', [KehadiranController::class, 'destroy']);
    Route::post('kehadiran/scan/{token}', [KehadiranController::class, 'scanQrCode']);
    Route::get('kehadiran/report', [KehadiranController::class, 'report']);

    // Rute baru untuk filter berdasarkan kursus
    Route::get('kursus/{kursus_id}/sesi', [SesiKehadiranController::class, 'getByKursus']);
});
