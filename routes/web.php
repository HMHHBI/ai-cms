<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/deploy-db', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate --force');
    return "Database Migrated Successfully!";
});

require __DIR__ . '/auth.php';
