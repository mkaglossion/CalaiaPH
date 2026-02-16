<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
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
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        
        // Generate password reset token
        $token = Str::random(60);
        $user->password_reset_token = $token;
        $user->password_reset_expires = now()->addHours(24);
        $user->save();

        // Send password reset email
        Mail::to($user->email)->send(new PasswordReset($user, $token));

        // Return JSON response for frontend
        return response()->json([
            'success' => true,
            'message' => 'Password reset link has been sent to your email address. Please check your inbox.',
        ]);
    }

    /**
     * Show the reset password form
     */
    public function showResetForm($token)
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

        return response()->json([
            'success' => true,
            'message' => 'Your password has been successfully reset. You can now log in with your new password.',
        ]);
    }
}
