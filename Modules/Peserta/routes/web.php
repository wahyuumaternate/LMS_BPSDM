<?php

use Illuminate\Support\Facades\Route;
use Modules\Peserta\Http\Controllers\PesertaController;

// Routes untuk Admin/Instruktur
Route::middleware('auth:admin_instruktur')->prefix('admin')->group(function () {
    
    // Peserta Management Routes - TANPA name prefix
    Route::resource('peserta', PesertaController::class)->parameters([
        'peserta' => 'pesertum'
    ]);
    
});