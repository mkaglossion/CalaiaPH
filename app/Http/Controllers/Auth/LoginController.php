<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            return redirect('/home')->with('error', 'Please verify your email before logging in.');
        }

        // Find verified user
        $user = User::where('email', $credentials['username'])
            ->orWhere('username', $credentials['username'])
            ->first();

        if (!$user) {
            return redirect('/home')->with('error', 'No account found with that email or username.');
        }

        // Check password - cast to string to handle SensitiveParameterValue objects
        $plainPassword = (string) $credentials['password'];
        
        try {
            $passwordIsValid = Hash::check($plainPassword, $user->password);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Password check failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'password_hash_start' => substr($user->password, 0, 30),
            ]);
            return redirect('/home')->with('error', 'An error occurred during login. Please try again.');
        }
        
        if (!$passwordIsValid) {
            return redirect('/home')->with('error', 'Incorrect password.');
        }

        // Log user in
        Auth::login($user);

        session()->save();

        return redirect('/home')->with('success', 'Welcome back! You have been logged in successfully.');

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
        return redirect('/home')->with('info', 'You have been logged out successfully. See you next time!');
    }
}
