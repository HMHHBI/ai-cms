<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
    public function index()
    {
        // Sirf Super Admin hi ye dekh sakay
        if (Auth::user()->role !== 'super_admin') {
            abort(403);
        }

        $companies = Company::withCount('users')->get();
        $pendingUsers = User::where('is_active', false)
            ->where('role', 'admin')
            ->with('company')
            ->get();

        return view('super-admin.dashboard', compact('companies', 'pendingUsers'));
    }

    public function approve(User $user)
    {
        if (Auth::user()->role !== 'super_admin') {
            abort(403);
        }

        $user->update(['is_active' => true]);

        return back()->with('status', "Account for {$user->name} activated successfully!");
    }
}
