<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\PendingUser;
use App\Services\AuthenticationRateLimiter;

class AdminAuthController extends Controller
{
    private const GENERIC_ADMIN_LOGIN_ERROR = 'These credentials do not match an admin account.';

    public function __construct(private AuthenticationRateLimiter $rateLimiter)
    {
    }

    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()?->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

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
        $throttleKey = $this->rateLimiter->key($request, $credentials['username'], 'admin-login');

        if ($this->rateLimiter->tooManyAttempts($throttleKey, 5)) {
            return redirect()->route('admin.login')->with(
                'error',
                'Too many login attempts. Please try again in '.$this->rateLimiter->availableIn($throttleKey).' seconds.'
            );
        }

        $pending = PendingUser::where('email', $credentials['username'])
            ->orWhere('username', $credentials['username'])
            ->first();

        if ($pending) {
            $this->rateLimiter->hit($throttleKey, 60);

            return redirect()->route('admin.login')->with('error', self::GENERIC_ADMIN_LOGIN_ERROR);
        }

        $user = User::where('email', $credentials['username'])
            ->orWhere('username', $credentials['username'])
            ->first();

        if (!$user) {
            $this->rateLimiter->hit($throttleKey, 60);

            return redirect()->route('admin.login')->with('error', self::GENERIC_ADMIN_LOGIN_ERROR);
        }

        if ($user->user_type !== 'admin') {
            $this->rateLimiter->hit($throttleKey, 60);

            return redirect()->route('admin.login')->with('error', self::GENERIC_ADMIN_LOGIN_ERROR);
        }

        $plainPassword = (string) $credentials['password'];
        try {
            $passwordIsValid = Hash::check($plainPassword, $user->password);
        } catch (\Exception $e) {
            Log::error('Password check failed (admin login)', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return redirect()->route('admin.login')->with('error', 'An error occurred during login. Please try again.');
        }

        if (!$passwordIsValid) {
            $this->rateLimiter->hit($throttleKey, 60);

            return redirect()->route('admin.login')->with('error', self::GENERIC_ADMIN_LOGIN_ERROR);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $this->rateLimiter->clear($throttleKey);
        session()->save();

        return redirect()->route('admin.dashboard')->with('success', 'Welcome back, admin!');
    }
}
