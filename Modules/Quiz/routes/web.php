<?php

use Illuminate\Support\Facades\Route;
use Modules\Quiz\Http\Controllers\QuizController;
use Modules\Quiz\Http\Controllers\SoalQuizController;
use Modules\Quiz\Http\Controllers\HasilQuizController;

/*
|--------------------------------------------------------------------------
| Web Routes for Quiz Module
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:admin_instruktur'])
    ->prefix('content')
    ->group(function () {
        // Quiz resource routes
        Route::resource('quizzes', QuizController::class);

        // Custom routes for SoalQuiz (must come BEFORE the resource route)
        Route::get('soal-quiz/quiz/{quizId}', [SoalQuizController::class, 'getByQuiz'])->name('soal-quiz.by-quiz');
        Route::get('soal-quiz/validate-options/{id}', [SoalQuizController::class, 'validateOptions'])->name('soal-quiz.validate-options');
        Route::get('soal-quiz/bulk/create', [SoalQuizController::class, 'createBulk'])->name('soal-quiz.create-bulk');
        Route::post('soal-quiz/bulk/store', [SoalQuizController::class, 'storeBulk'])->name('soal-quiz.store-bulk');

        // SoalQuiz resource routes
        Route::resource('soal-quiz', SoalQuizController::class);

        // Routes for instructors to try quizzes
        Route::get('/quizzes/{id}/try', [QuizController::class, 'tryQuiz'])->name('quizzes.try');
        Route::post('/quizzes/{id}/try', [QuizController::class, 'processTryQuiz'])->name('quizzes.process-try');
        Route::get('/quizzes/{id}/try-result', [QuizController::class, 'tryQuizResult'])->name('quizzes.try-result');

        // HasilQuiz routes
        Route::prefix('hasil-quiz')
            ->name('hasil-quiz.')
            ->group(function () {
                Route::get('/', [HasilQuizController::class, 'index'])->name('index');
                Route::get('/{id}', [HasilQuizController::class, 'show'])->name('show');
                Route::get('/peserta/{peserta_id}', [HasilQuizController::class, 'pesertaOverview'])->name('peserta-overview');
                Route::get('/quiz/{quiz_id}', [HasilQuizController::class, 'quizOverview'])->name('quiz-overview');
            });
    });
