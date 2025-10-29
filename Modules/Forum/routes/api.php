<?php

use Illuminate\Support\Facades\Route;
use Modules\Forum\Http\Controllers\API\ForumController;

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
    // Forum Routes
    Route::get('forum', [ForumController::class, 'index']);
    Route::post('forum', [ForumController::class, 'store']);
    Route::get('forum/{id}', [ForumController::class, 'show']);
    Route::put('forum/{id}', [ForumController::class, 'update']);
    Route::delete('forum/{id}', [ForumController::class, 'destroy']);

    // Toggle status forum
    Route::put('forum/{id}/toggle-status', [ForumController::class, 'toggleStatus']);

    // Get forums by kursus
    Route::get('kursus/{kursus_id}/forum', [ForumController::class, 'getByKursus']);
});
