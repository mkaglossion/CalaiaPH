<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\PendingUser;
use App\Services\AuthenticationRateLimiter;

class LoginController extends Controller
{
    private const GENERIC_LOGIN_ERROR = 'These credentials do not match our records.';

    public function __construct(private AuthenticationRateLimiter $rateLimiter)
    {
    }

    /**
     * Handle login request from modal
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        $credentials = [
            'username' => trim((string) $validated['username']),
            'password' => (string) $validated['password'],
        ];
        $throttleKey = $this->rateLimiter->key($request, $credentials['username'], 'customer-login');

        if ($this->rateLimiter->tooManyAttempts($throttleKey, 5)) {
            return redirect()->route('home')->with(
                'error',
                'Too many login attempts. Please try again in '.$this->rateLimiter->availableIn($throttleKey).' seconds.'
            );
        }

        // Check if user is pending (not verified)
        $pending = PendingUser::where('email', $credentials['username'])
            ->orWhere('username', $credentials['username'])
            ->first();

        if ($pending) {
            $this->rateLimiter->hit($throttleKey, 60);

            return redirect()->route('home')->with('error', self::GENERIC_LOGIN_ERROR);
        }

        // Find verified user
        $user = User::where('email', $credentials['username'])
            ->orWhere('username', $credentials['username'])
            ->first();

        if (!$user) {
            $this->rateLimiter->hit($throttleKey, 60);

            return redirect()->route('home')->with('error', self::GENERIC_LOGIN_ERROR);
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
            ]);
            return redirect()->route('home')->with('error', 'An error occurred during login. Please try again.');
        }
        
        if (!$passwordIsValid) {
            $this->rateLimiter->hit($throttleKey, 60);

            return redirect()->route('home')->with('error', self::GENERIC_LOGIN_ERROR);
        }

        // Log user in
        Auth::login($user);
        $request->session()->regenerate();
        $this->rateLimiter->clear($throttleKey);

        session()->save();

        return redirect()->route('home')->with('success', 'Welcome back! You have been logged in successfully.');

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
        return redirect()->route('home')->with('info', 'You have been logged out successfully. See you next time!');
    }
}
