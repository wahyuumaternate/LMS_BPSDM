<?php

use Illuminate\Support\Facades\Route;
use Modules\Materi\Http\Controllers\MateriController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('materis', MateriController::class)->names('materi');
});
