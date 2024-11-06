<?php

use App\Http\Controllers\Api\WaterQualityDataController;
use App\Http\Controllers\WaterQualityController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('optimal-fish', 'optimal-fish')
    ->middleware(['auth'])
    ->name('optimal-fish');

Route::view('historical-data', 'historical-data')
    ->middleware(['auth'])
    ->name('historical-data');

Route::post('get-water-quality-data', [WaterQualityDataController::class, 'store']);



require __DIR__.'/auth.php';
