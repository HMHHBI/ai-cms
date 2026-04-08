<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PublicTicketController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\StaffController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;

Route::view('/', 'welcome');

Route::get('/dashboard', function () {
    $user = Auth::user();

    // Agar Super Admin hai toh usay uske panel bhej do
    if ($user->role === 'super_admin') {
        return redirect()->route('super.dashboard');
    }

    // Baqi sab (Admin/Staff) ke liye normal dashboard
    return view('dashboard');
})->middleware(['auth', 'verified', 'check_status'])->name('dashboard');

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

Route::get('/super-admin/dashboard', [SuperAdminController::class, 'index'])
    ->middleware(['auth', 'check_status'])
    ->name('super.dashboard');
Route::post('/super-admin/approve/{user}', [SuperAdminController::class, 'approve'])->middleware(['auth', 'check_status'])->name('super.approve');

Route::middleware(['auth', 'check_status'])->group(function () {
    // Staff Management Routes (Sirf Admins ke liye)
    Route::middleware(['can:access-admin-panel'])->group(function () {
        Route::get('/admin/staff', [StaffController::class, 'index'])->name('admin.staff.index');
        Route::post('/admin/staff', [StaffController::class, 'store'])->name('admin.staff.store');
        Route::delete('/admin/staff/{user}', [StaffController::class, 'destroy'])->name('admin.staff.destroy');
    });
});

Route::get('/test-email', function () {
    try {
        Mail::raw('Bhai, email sahi chal rahi hai!', function ($message) {
            $message->to('hmhhbi@gmail.com')
                ->subject('Laravel Test Email');
        });
        return "Email Sent Successfully!";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

RateLimiter::for('tickets', function (Request $request) {
    return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
});

require __DIR__ . '/auth.php';
