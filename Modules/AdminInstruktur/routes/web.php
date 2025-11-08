<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminInstruktur\Http\Controllers\AdminInstrukturController;


Route::get('/', function () {
    return view('welcome');
});
