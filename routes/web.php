<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicTicketController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'check_status'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth', 'check_status'])
    ->name('profile');

Route::get('/deploy-db', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate --force');
    return "Database Migrated Successfully!";
});

// Ye route login ke bahar hoga
Route::get('/support/{company_slug}', [PublicTicketController::class, 'show'])->name('public.support');
Route::post('/support/{company_slug}', [PublicTicketController::class, 'store'])->middleware('throttle:tickets')->name('public.support.store');

RateLimiter::for('tickets', function (Request $request) {
    return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
});

require __DIR__ . '/auth.php';
