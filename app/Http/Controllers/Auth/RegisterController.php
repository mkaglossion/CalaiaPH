<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\VerifyEmail;
use App\Mail\WelcomeEmail;

class RegisterController extends Controller
{
    /**
     * STEP 1: Handle registration (store pending user + send email)
     */
    public function registerStepOne(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:pending_users,email|unique:users,email',
            'username'   => 'required|string|max:255|unique:pending_users,username|unique:users,username',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        $token = Str::random(64);

        // Store temporarily in pending_users
        PendingUser::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'username'   => $validated['username'],
            'password'   => Hash::make($validated['password']),
            'token'      => $token,
        ]);

        // Send verification email
        Mail::to($validated['email'])->send(new VerifyEmail($validated['first_name'], $token));

        // Return JSON if AJAX, or redirect with flash message
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Verification email sent! Please check your inbox.',
            ]);
        }

        return back()->with('success', 'Please check your email to verify your account before logging in.');
    }


    /**
     * STEP 2: Verify email link
     */
    public function verifyEmail($token)
    {
        $pending = PendingUser::where('token', $token)->first();

        if (!$pending) {
            return redirect('/login')->with('error', 'Invalid or expired verification link.');
        }

        // Move user to the main users table
        $user = User::create([
            'user_type'  => 'client',
            'first_name' => $pending->first_name,
            'last_name'  => $pending->last_name,
            'email'      => $pending->email,
            'username'   => $pending->username,
            'password'   => $pending->password, // already hashed
        ]);

        // Delete pending user
        $pending->delete();

        // ✅ Send welcome email
        Mail::to($user->email)->send(new WelcomeEmail($user->first_name));

        return redirect('/login')->with('success', 'Your account has been verified! A welcome email has been sent — you can now log in.');
    }
}
