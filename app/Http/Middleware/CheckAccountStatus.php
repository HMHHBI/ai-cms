<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check karein ke user login hai aur active nahi hai
        if (Auth::check() && !Auth::user()->is_active) {
            Auth::logout();

            // Session invalidate aur regenerate karein security ke liye
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Aapka account abhi pending hai. Admin se rabta karein.');
        }
        return $next($request);
    }
}
