
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Quiz routes
    Route::apiResource('quizzes', QuizController::class)->names('quiz');
    
    // Soal Quiz routes
    Route::post('soal-quiz/bulk', [SoalQuizController::class, 'bulkCreate'])->name('soal-quiz.bulk');
    Route::apiResource('soal-quiz', SoalQuizController::class)->names('soal-quiz');
    
    // Quiz Option routes
    Route::post('quiz-options/bulk', [QuizOptionController::class, 'bulkCreate'])->name('quiz-option.bulk');
    Route::get('quiz-options/question/{question_id}', [QuizOptionController::class, 'getByQuestion'])->name('quiz-option.by-question');
    Route::apiResource('quiz-options', QuizOptionController::class)->names('quiz-option');
    
    // Quiz Result routes
    Route::post('quiz-results/start', [QuizResultController::class, 'startQuiz'])->name('quiz-result.start');
    Route::get('quiz-results/peserta/{peserta_id}', [QuizResultController::class, 'getByPeserta'])->name('quiz-result.by-peserta');
    Route::apiResource('quiz-results', QuizResultController::class)->names('quiz-result');

    
});