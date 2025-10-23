<?php

use Illuminate\Support\Facades\Route;
use Modules\Forum\Http\Controllers\ForumController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('forums', ForumController::class)->names('forum');
});
