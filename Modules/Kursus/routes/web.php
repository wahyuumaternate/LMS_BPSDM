<?php

use Illuminate\Support\Facades\Route;
use Modules\Kursus\Http\Controllers\KursusController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('courses', KursusController::class)->names('course');
    Route::get("course/table", [KursusController::class, 'table'])->name('course.table');

    Route::get('course/search-instruktur', [KursusController::class, 'search_instruktur'])->name('search.instruktur');

});
