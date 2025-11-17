<?php

use Illuminate\Support\Facades\Route;
use Modules\Kursus\Http\Controllers\KursusController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('courses', KursusController::class)->names('course');
    Route::get("course/{id}/prasyarat", [KursusController::class, 'prasyarat'])->name('course.prasyarat');
    Route::get("course/{id}/modul", [KursusController::class, 'modul'])->name('course.modul');

    Route::post("prasyarat", [KursusController::class, 'store_prasyarat'])->name('prasyarat.store');
    Route::patch("prasyarat/{id}", [KursusController::class, 'update_prasyarat'])->name('prasyarat.update');
    Route::delete("prasyarat/{id}", [KursusController::class, 'delete_prasyarat'])->name('prasyarat.destroy');

    Route::get("course/table", [KursusController::class, 'table'])->name('course.table');
    Route::get('course/search-instruktur', [KursusController::class, 'search_instruktur'])->name('search.instruktur');

    Route::get('/course/{id}/materi', [KursusController::class, 'materi'])->name('course.materi');
    Route::get('/course/{id}/tugas', [KursusController::class, 'tugas'])->name('course.tugas');
    Route::get('/course/{id}/ujian', [KursusController::class, 'ujian'])->name('course.ujian');
    Route::get('/course/{id}/forum', [KursusController::class, 'forum'])->name('course.forum');
    Route::get('/course/{id}/kuis', [KursusController::class, 'kuis'])->name('course.kuis');
    Route::get('/course/{id}/peserta', [KursusController::class, 'peserta'])->name('course.peserta');


    // Kursus Participant Routes (with better naming convention)
    Route::prefix('kursus')->name('kursus.')->group(function () {

        Route::prefix('/{kursus}/peserta')->name('peserta.')->group(function () {

            // Update participant status
            Route::put('/{peserta}/status', [KursusController::class, 'updateStatus'])
                ->name('status.update');

            // Update participant grade (nilai)
            Route::put('/{peserta}/nilai', [KursusController::class, 'updateNilai'])
                ->name('nilai.update');

            // Export participants to Excel
            Route::get('/export', [KursusController::class, 'exportPeserta'])
                ->name('export');

            // Bulk update status (optional)
            Route::post('/bulk-status', [KursusController::class, 'bulkUpdateStatus'])
                ->name('bulk.status');
        });
    });
});
