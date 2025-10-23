<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminInstruktur\Http\Controllers\AdminInstrukturController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('admininstrukturs', AdminInstrukturController::class)->names('admininstruktur');
});
