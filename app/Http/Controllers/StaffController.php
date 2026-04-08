<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function index()
    {
        // Sirf apni company ka staff dikhana hai
        $staff = User::where('company_id', Auth::user()->company_id)
            ->where('role', 'staff')
            ->get();

        return view('admin.staff.index', compact('staff'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => Auth::user()->company_id, // Automatic current admin ki company
            'role' => 'staff',
            'is_active' => true, // Admin khud add kar raha hai toh active hi hoga
        ]);

        return back()->with('status', 'Staff member added successfully!');
    }

    public function destroy(User $user)
    {
        // Security check: Kya ye staff usi admin ki company ka hai?
        if ($user->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $user->delete();
        return back()->with('status', 'Staff member removed.');
    }
}