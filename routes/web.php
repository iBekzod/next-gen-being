<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\BlogController;

// Route::get('/', [LandingPageController::class, 'index']);
Route::post('/subscribe', [LandingPageController::class, 'store'])->name('subscribe');

Route::get('/', [BlogController::class, 'index'])->name('home');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
