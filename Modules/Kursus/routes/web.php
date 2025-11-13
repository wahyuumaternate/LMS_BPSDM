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

    Route::post("modul", [KursusController::class, 'store_modul'])->name('modul.store');
    Route::patch("modul/{id}", [KursusController::class, 'update_modul'])->name('modul.update');
    Route::delete("modul/{id}", [KursusController::class, 'destroy_modul'])->name('modul.destroy');

    Route::post("materi-modul", [KursusController::class, 'store_materi'])->name('modul.materi.store');
    Route::patch("materi-modul/{id}", [KursusController::class, 'update_materi'])->name('modul.materi.update');
    Route::delete("materi-modul/{id}", [KursusController::class, 'destroy_materi'])->name('modul.materi.destroy');
    
    Route::get("course/table", [KursusController::class, 'table'])->name('course.table');
    Route::get('course/search-instruktur', [KursusController::class, 'search_instruktur'])->name('search.instruktur');

});
