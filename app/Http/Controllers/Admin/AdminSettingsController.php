<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
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
        // Handle checkbox values - unchecked checkboxes don't send data, so we need to set them to false
        $data = [
            'email_low_inventory_alerts' => $request->has('email_low_inventory_alerts') ? true : false,
            'email_pending_payments' => $request->has('email_pending_payments') ? true : false,
            'email_bookings' => $request->has('email_bookings') ? true : false,
            'email_user_signups' => $request->has('email_user_signups') ? true : false,
        ];

        // Add optional fields if provided
        if ($request->has('theme_preference')) {
            $data['theme_preference'] = $request->input('theme_preference');
        }
        if ($request->has('language')) {
            $data['language'] = $request->input('language');
        }

        // Validate the data
        $validated = $request->validate([
            'theme_preference' => 'nullable|in:light,dark',
            'language' => 'nullable|in:en,es,fr,tl',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $preferences = $user->adminPreference ?? AdminPreference::create(['user_id' => $user->id]);
        $preferences->update($data);

        return back()->with('success', 'Preferences updated successfully.');
    }

    public function showActivityLog(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:100'],
            'admin' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $search = trim((string) ($filters['q'] ?? ''));

        $activities = AdminActivityLog::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('description', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('model_type', 'like', "%{$search}%")
                        ->orWhere('model_id', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery
                                ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when(! empty($filters['action']), fn ($query) => $query->where('action', $filters['action']))
            ->when(! empty($filters['admin']), fn ($query) => $query->where('user_id', $filters['admin']))
            ->when(! empty($filters['from']), fn ($query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when(! empty($filters['to']), fn ($query) => $query->whereDate('created_at', '<=', $filters['to']))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $actions = AdminActivityLog::query()
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');
        $admins = User::query()
            ->where('user_type', 'admin')
            ->whereHas('adminActivityLogs')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);
        $stats = [
            'total' => AdminActivityLog::count(),
            'today' => AdminActivityLog::whereDate('created_at', today())->count(),
            'filtered' => $activities->total(),
            'latest' => AdminActivityLog::latest()->value('created_at'),
        ];

        return view('admin.settings.activity-log', compact('activities', 'actions', 'admins', 'filters', 'stats'));
    }
}
