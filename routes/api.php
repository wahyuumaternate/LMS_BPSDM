<?php

use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    return response()->json(['message' => 'Endpoint not found'], 404);
});
