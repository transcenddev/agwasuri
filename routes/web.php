<?php

use App\Http\Controllers\Api\WaterQualityDataController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('account_management', 'account_management')
    ->middleware(['auth', 'verified', 'isAdmin'])
    ->name('account_management');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('optimal-fish', 'optimal-fish')
    ->middleware(['auth'])
    ->name('optimal-fish');

Route::view('historical-data', 'historical-data')
    ->middleware(['auth'])
    ->name('historical-data');

// Theme setter endpoint used by client to ensure server writes theme cookie
Route::post('/set-theme', [ThemeController::class, 'set'])->name('theme.set');

Route::post('store-water-quality-data', [WaterQualityDataController::class, 'store']);

// API endpoint used by client-side chart polling and snapshot requests
Route::get('api/water-quality/latest', [WaterQualityDataController::class, 'latest']);

require __DIR__.'/auth.php';
