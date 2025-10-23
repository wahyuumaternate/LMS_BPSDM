<?php

use Illuminate\Support\Facades\Route;
use Modules\Peserta\Http\Controllers\PesertaController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('pesertas', PesertaController::class)->names('peserta');
});
