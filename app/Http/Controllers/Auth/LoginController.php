<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PendingUser;

class LoginController extends Controller
{
    /**
     * Handle login request from modal
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        $credentials = $request->only('username', 'password');

        // Check if user is pending (not verified)
        $pending = PendingUser::where('email', $credentials['username'])
            ->orWhere('username', $credentials['username'])
            ->first();

        if ($pending) {
            return response()->json([
                'message' => 'Please verify your email before logging in.',
            ], 403);
        }

        // Find verified user
        $user = User::where('email', $credentials['username'])
            ->orWhere('username', $credentials['username'])
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'No account found with that email or username.',
            ], 404);
        }

        // Check password
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Incorrect password.',
            ], 401);
        }

        // Log user in
        Auth::login($user);

        return redirect('/')->with('success', 'You have been logged in successfully.');

    }

    /**
     * Logout user (supports both normal and AJAX)
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ✅ Handle AJAX (fetch) logout
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'You have been logged out successfully.',
                'redirect' => url('/'),
            ]);
        }

        // ✅ Handle normal browser logout
        return redirect('/')->with('success', 'You have been logged out successfully.');
    }
}
