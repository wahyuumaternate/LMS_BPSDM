<?php

use Illuminate\Support\Facades\Route;
use Modules\Kehadiran\Http\Controllers\KehadiranController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('kehadirans', KehadiranController::class)->names('kehadiran');
});
