<?php

use Illuminate\Support\Facades\Route;
use Modules\Sertifikat\Http\Controllers\API\SertifikatController;

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
        
       

        // =====================================================
        // SERTIFIKAT ROUTES - Untuk peserta
        // =====================================================
        Route::prefix('sertifikat')->group(function () {
            // List & Summary
            Route::get('/', [SertifikatController::class, 'index']);
            Route::get('/summary', [SertifikatController::class, 'summary']);
            
            // Check Availability
            Route::get('/check/{kursus_id}', [SertifikatController::class, 'checkAvailability']);
            
            // By Kursus
            Route::get('/kursus/{kursus_id}', [SertifikatController::class, 'getByKursus']);
            
            // Detail & Actions
            Route::get('/{id}', [SertifikatController::class, 'show']);
            Route::get('/{id}/download', [SertifikatController::class, 'download'])
                ->name('api.student.sertifikat.download');
            Route::get('/{id}/view', [SertifikatController::class, 'view'])
                ->name('api.student.sertifikat.view');
        });
    });
});