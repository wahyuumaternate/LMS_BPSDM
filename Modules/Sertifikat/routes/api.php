<?php


use Illuminate\Support\Facades\Route;
use Modules\Sertifikat\Http\Controllers\API\TemplateSertifikatController;
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

Route::prefix('v1')->group(function () {
    // Public routes
    Route::get('sertifikat/verify', [SertifikatController::class, 'verify'])->name('sertifikat.verify');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Template Sertifikat Routes
        Route::get('template-sertifikat', [TemplateSertifikatController::class, 'index']);
        Route::post('template-sertifikat', [TemplateSertifikatController::class, 'store']);
        Route::get('template-sertifikat/{id}', [TemplateSertifikatController::class, 'show']);
        Route::post('template-sertifikat/{id}', [TemplateSertifikatController::class, 'update']);
        Route::delete('template-sertifikat/{id}', [TemplateSertifikatController::class, 'destroy']);
        Route::get('template-sertifikat/preview/{id}', [TemplateSertifikatController::class, 'preview']);

        // Sertifikat Routes
        Route::get('sertifikat', [SertifikatController::class, 'index']);
        Route::post('sertifikat', [SertifikatController::class, 'store']);
        Route::get('sertifikat/{id}', [SertifikatController::class, 'show']);
        Route::put('sertifikat/{id}', [SertifikatController::class, 'update']);
        Route::delete('sertifikat/{id}', [SertifikatController::class, 'destroy']);

        // Special Sertifikat Routes
        Route::get('sertifikat/by-peserta/{peserta_id}', [SertifikatController::class, 'getByPeserta']);
        Route::get('sertifikat/by-kursus/{kursus_id}', [SertifikatController::class, 'getByKursus']);
        Route::post('sertifikat/send-email/{id}', [SertifikatController::class, 'sendEmail']);
    });
});
