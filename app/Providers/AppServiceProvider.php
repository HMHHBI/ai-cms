<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 💡 FORCE HTTPS: Agar app production par ho toh HTTPS links banayein
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        Gate::define('access-admin-panel', function (User $user) {
            return in_array($user->role, ['admin', 'super_admin']);
        });
    }
}
