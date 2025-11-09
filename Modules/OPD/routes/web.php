<?php

use Illuminate\Support\Facades\Route;
use Modules\OPD\Http\Controllers\OPDController;

/*
|--------------------------------------------------------------------------
| OPD Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::prefix('opd')->group(function () {
        Route::get('/', [OPDController::class, 'index'])->name('opd.index');
        Route::post('/', [OPDController::class, 'store'])->name('opd.store');
        Route::get('/{id}', [OPDController::class, 'show'])->name('opd.show');
        Route::put('/{id}', [OPDController::class, 'update'])->name('opd.update');
        Route::delete('/{id}', [OPDController::class, 'destroy'])->name('opd.destroy');
    });
});
