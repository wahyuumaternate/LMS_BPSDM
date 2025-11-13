<?php

use Illuminate\Support\Facades\Route;
use Modules\Modul\Http\Controllers\ModulController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Route::resource('moduls', ModulController::class)->names('modul');
});
