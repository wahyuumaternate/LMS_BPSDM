<?php

use Illuminate\Support\Facades\Route;
use Modules\Quiz\Http\Controllers\API\StudentQuizController;

/*
|--------------------------------------------------------------------------
| API Routes - Student Quiz
|--------------------------------------------------------------------------
| Routes untuk peserta mengikuti quiz
| Menggunakan guard 'peserta' dengan Sanctum
*/

Route::prefix('v1/student')->middleware(['auth:peserta'])->group(function () {
    
    // Get daftar quiz
    Route::get('quizzes', [StudentQuizController::class, 'index'])
        ->name('student.quizzes.index');
    
    // Get detail quiz
    Route::get('quizzes/{id}', [StudentQuizController::class, 'show'])
        ->name('student.quizzes.show');
    
    // Start quiz - mendapatkan soal
    Route::post('quizzes/{id}/start', [StudentQuizController::class, 'startQuiz'])
        ->name('student.quizzes.start');
    
    // Submit jawaban quiz
    Route::post('quizzes/submit', [StudentQuizController::class, 'submitQuiz'])
        ->name('student.quizzes.submit');
    
    // Get riwayat attempts
    Route::get('my-attempts', [StudentQuizController::class, 'myAttempts'])
        ->name('student.my-attempts');
    
    // Get detail hasil quiz
    Route::get('results/{id}', [StudentQuizController::class, 'getResult'])
        ->name('student.results.show');
    
    // Get best attempt untuk quiz tertentu
    Route::get('quizzes/{id}/my-best-attempt', [StudentQuizController::class, 'myBestAttempt'])
        ->name('student.quizzes.best-attempt');
});