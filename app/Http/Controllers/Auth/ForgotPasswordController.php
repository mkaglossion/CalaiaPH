<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordReset;
use App\Models\User;
use App\Services\AuthenticationRateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    private const GENERIC_RESET_MESSAGE = 'If an account exists for this email address, a password reset link has been sent.';

    public function __construct(private AuthenticationRateLimiter $rateLimiter)
    {
    }

    /**
     * Show the forgot password form
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = Str::lower(trim((string) $validated['email']));
        $throttleKey = $this->rateLimiter->key($request, $email, 'password-reset');

        if ($this->rateLimiter->tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many password reset attempts. Please try again in '.$this->rateLimiter->availableIn($throttleKey).' seconds.',
            ], 429);
        }

        $this->rateLimiter->hit($throttleKey, 600);
        $user = User::where('email', $email)->first();

        if ($user) {
            // Generate password reset token
            $token = Str::random(60);
            $user->password_reset_token = $token;
            $user->password_reset_expires = now()->addHours(24);
            $user->save();

            // Send password reset email
            Mail::to($user->email)->send(new PasswordReset($user, $token));
        }

        // Return JSON response for frontend
        return response()->json([
            'success' => true,
            'message' => self::GENERIC_RESET_MESSAGE,
        ]);
    }

    /**
     * Show the reset password form
     */
    public function showResetForm(string $token)
    {
        $user = User::where('password_reset_token', $token)
            ->where('password_reset_expires', '>', now())
            ->first();

        if (!$user) {
            return redirect()->route('forgot.password')
                ->with('error', 'Password reset token is invalid or has expired.');
        }

        return view('auth.reset-password', ['token' => $token, 'email' => $user->email]);
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)
            ->where('password_reset_token', $request->token)
            ->where('password_reset_expires', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Password reset token is invalid or has expired.',
            ], 400);
        }

        // Update password and clear reset token
        $user->password = bcrypt($request->password);
        $user->password_reset_token = null;
        $user->password_reset_expires = null;
        $user->save();

        if (Auth::id() === $user->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Your password has been successfully reset. You can now log in with your new password.',
        ]);
    }
}
