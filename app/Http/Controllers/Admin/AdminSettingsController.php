<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPreference;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSettingsController extends Controller
{
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user();
        $preferences = $user->getOrCreateAdminPreference();

        return view('admin.settings.profile', compact('user', 'preferences'));
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'mobile_number' => 'nullable|string|max:20',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->update(['password' => bcrypt($validated['password'])]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'email_low_inventory_alerts' => 'boolean',
            'email_pending_payments' => 'boolean',
            'email_bookings' => 'boolean',
            'email_user_signups' => 'boolean',
            'theme_preference' => 'in:light,dark',
            'language' => 'in:en,es,fr,tl',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $preferences = $user->adminPreference ?? new AdminPreference(['user_id' => $user->id]);
        $preferences->update($validated);

        return back()->with('success', 'Preferences updated successfully.');
    }

    public function showActivityLog()
    {
        /** @var User $user */
        $user = Auth::user();
        $activities = $user->adminActivityLogs()
            ->latest()
            ->paginate(20);

        return view('admin.settings.activity-log', compact('activities'));
    }
}
