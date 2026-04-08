<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $req)
    {
        $req->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'company_name' => ['required', 'string', 'max:255', 'unique:companies,name'],
        ]);

        $company = Company::create([
            'name' => $req->company_name,
            'slug' => Str::slug($req->company_name),
        ]);

        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => Hash::make($req->password),
            'company_id' => $company->id, // Automatic assignment
            'role' => 'admin',
            'is_active' => false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Registration successful. Please wait for admin approval.',
            'user' => $user
        ]);
    }

    public function login(Request $req)
    {
        $user = User::where('email', $req->email)->first();

        if (!$user || !Hash::check($req->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'Aapka account abhi pending hai. Admin approval ka intezar karein.'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'user' => $user,
            'token' => $user->createToken('api-token')->plainTextToken
        ]);
    }

    public function logout(Request $req)
    {
        $req->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out'
        ]);
    }
}