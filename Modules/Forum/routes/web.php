<?php

use Illuminate\Support\Facades\Route;
use Modules\Forum\Http\Controllers\ForumController;

// Di routes/web.php
Route::post('/forum', [ForumController::class, 'store'])->name('forum.store');
Route::patch('/forum/{id}', [ForumController::class, 'update'])->name('forum.update');
Route::delete('/forum/{id}', [ForumController::class, 'destroy'])->name('forum.destroy');
