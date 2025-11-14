<?php

use Illuminate\Support\Facades\Route;
use Modules\Modul\Http\Controllers\ModulController;

// Di routes/web.php atau routes untuk module
Route::post('/modul', [ModulController::class, 'store'])->name('modul.store');
Route::patch('/modul/{id}', [ModulController::class, 'update'])->name('modul.update');
Route::delete('/modul/{id}', [ModulController::class, 'destroy'])->name('modul.destroy');
