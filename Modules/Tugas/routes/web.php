<?php

use Illuminate\Support\Facades\Route;
use Modules\Tugas\Http\Controllers\TugasSubmissionController;
use Modules\Tugas\Http\Controllers\TugasController;
use Modules\Tugas\Http\Controllers\SubmissionController;

/*
|--------------------------------------------------------------------------
| Web Routes for Tugas Module
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin_instruktur'])->group(function () {
    // Resource routes
    Route::resource('content/assessments', TugasController::class)->names([
        'index' => 'tugas.index',
        'create' => 'tugas.create',
        'store' => 'tugas.store',
        'show' => 'tugas.show',
        'edit' => 'tugas.edit',
        'update' => 'tugas.update',
        'destroy' => 'tugas.destroy',
    ]);

    // Additional routes
    Route::post('content/assessments/{id}/toggle-publish', [TugasController::class, 'togglePublish'])->name('tugas.toggle-publish');
    Route::get('content/assessments/{id}/submissions-tugas', [TugasController::class, 'submissions'])->name('tugas.submission');
    Route::post('content/submissions/{id}/nilai', [TugasSubmissionController::class, 'nilai'])->name('tugas.submission.nilai');
});


/*
|--------------------------------------------------------------------------
| Web Routes for Tugas Submission Module
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin_instruktur'])->group(function () {
    // Resource routes
    Route::resource('content/submissions', TugasSubmissionController::class)->names([
        'index' => 'submission.index',
        'create' => 'submission.create',
        'store' => 'submission.store',
        'show' => 'submission.show',
        'edit' => 'submission.edit',
        'update' => 'submission.update',
        'destroy' => 'submission.destroy',
    ]);

    // Additional routes
    Route::post('content/submissions/{id}/grade', [TugasSubmissionController::class, 'grade'])->name('submission.grade');
    Route::post('content/submissions/{id}/return', [TugasSubmissionController::class, 'returnForRevision'])->name('submission.return');
    Route::get('content/submissions/{id}/download', [TugasSubmissionController::class, 'downloadFile'])->name('submission.download');
    Route::get('content/submissions/peserta/{pesertaId}', [TugasSubmissionController::class, 'byPeserta'])->name('submission.by-peserta');
    Route::get('content/submissions/tugas/{tugasId}', [TugasSubmissionController::class, 'byTugas'])->name('submission.by-tugas');
});
