<?php

use Illuminate\Support\Facades\Route;
use Modules\Tugas\Http\Controllers\API\TugasSubmissionController;
use Modules\Tugas\Http\Controllers\API\TugasController;


Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Tugas routes
    Route::apiResource('tugas', TugasController::class)->names('tugas');

    // Tugas Submission routes
    Route::put('tugas-submissions/{id}/grade', [TugasSubmissionController::class, 'grade'])->name('tugas-submission.grade');
    Route::apiResource('tugas-submissions', TugasSubmissionController::class)->names('tugas-submission');
});
