<?php

use Illuminate\Support\Facades\Route;
use Modules\SesiKehadiran\Http\Controllers\SesiKehadiranController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('sesikehadirans', SesiKehadiranController::class)->names('sesikehadiran');
});
