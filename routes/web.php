<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicTicketController;

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

// Ye route login ke bahar hoga
Route::get('/support/{company_slug}', [PublicTicketController::class, 'show'])->name('public.support');
Route::post('/support/{company_slug}', [PublicTicketController::class, 'store'])->name('public.support.store');

require __DIR__ . '/auth.php';
