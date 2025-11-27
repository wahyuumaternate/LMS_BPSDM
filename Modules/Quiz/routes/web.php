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
        // ✅ SOAL-QUIZ ROUTES - EXPLICIT
        Route::get('soal-quiz', [SoalQuizController::class, 'index'])->name('soal-quiz.index');
        Route::get('soal-quiz/create', [SoalQuizController::class, 'create'])->name('soal-quiz.create');
        Route::post('soal-quiz', [SoalQuizController::class, 'store'])->name('soal-quiz.store');
        Route::get('soal-quiz/{id}', [SoalQuizController::class, 'show'])->name('soal-quiz.show');
        Route::get('soal-quiz/{id}/edit', [SoalQuizController::class, 'edit'])->name('soal-quiz.edit');
        Route::put('soal-quiz/{id}', [SoalQuizController::class, 'update'])->name('soal-quiz.update');
        Route::delete('soal-quiz/{id}', [SoalQuizController::class, 'destroy'])->name('soal-quiz.destroy');

        // Custom routes
        Route::get('soal-quiz/quiz/{quizId}', [SoalQuizController::class, 'getByQuiz'])->name('soal-quiz.by-quiz');
        Route::get('soal-quiz/validate-options/{id}', [SoalQuizController::class, 'validateOptions'])->name('soal-quiz.validate-options');
        Route::get('soal-quiz/bulk/create', [SoalQuizController::class, 'createBulk'])->name('soal-quiz.create-bulk');
        Route::post('soal-quiz/bulk/store', [SoalQuizController::class, 'storeBulk'])->name('soal-quiz.store-bulk');

        // ✅ QUIZ ROUTES
        Route::resource('quizzes', QuizController::class);

        // Quiz try routes
        Route::get('/quizzes/{id}/try', [QuizController::class, 'tryQuiz'])->name('quizzes.try');
        Route::post('/quizzes/{id}/try', [QuizController::class, 'processTryQuiz'])->name('quizzes.process-try');
        Route::get('/quizzes/{id}/try-result', [QuizController::class, 'tryQuizResult'])->name('quizzes.try-result');

       Route::prefix('hasil-quiz')->name('hasil-quiz.')->group(function () {

         // ⚠️ ROUTE EXPORT HARUS PALING ATAS!
           Route::get('/export', [HasilQuizController::class, 'export'])->name('export');
            // Route dengan REQUIRED quiz_id parameter
            Route::get('/{quizId}', [HasilQuizController::class, 'index'])->name('index');
            
            // Route untuk detail hasil (menampilkan semua attempts)
            Route::get('/detail/{id}', [HasilQuizController::class, 'show'])->name('show');
            
            // Route untuk quiz overview
            Route::get('/quiz/{quizId}/overview', [HasilQuizController::class, 'quizOverview'])->name('quiz-overview');
            
            // Route untuk peserta overview
            Route::get('/peserta/{pesertaId}/overview', [HasilQuizController::class, 'pesertaOverview'])->name('peserta-overview');
            
           

            // Route untuk delete
            Route::delete('/{id}', [HasilQuizController::class, 'destroy'])->name('destroy');
        });
    });
