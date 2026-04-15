<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\PendingUser;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()?->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        $credentials = $request->only('username', 'password');

        $pending = PendingUser::where('email', $credentials['username'])
            ->orWhere('username', $credentials['username'])
            ->first();

        if ($pending) {
            return redirect()->route('admin.login')->with('error', 'Please verify your email before logging in.');
        }

        $user = User::where('email', $credentials['username'])
            ->orWhere('username', $credentials['username'])
            ->first();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'No account found with that email or username.');
        }

        if ($user->user_type !== 'admin') {
            return redirect()->route('admin.login')->with('error', 'This login is for admin users only.');
        }

        $plainPassword = (string) $credentials['password'];
        try {
            $passwordIsValid = Hash::check($plainPassword, $user->password);
        } catch (\Exception $e) {
            Log::error('Password check failed (admin login)', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return redirect()->route('admin.login')->with('error', 'An error occurred during login. Please try again.');
        }

        if (!$passwordIsValid) {
            return redirect()->route('admin.login')->with('error', 'Incorrect password.');
        }

        Auth::login($user);
        session()->save();

        return redirect()->route('admin.dashboard')->with('success', 'Welcome back, admin!');
    }
}
