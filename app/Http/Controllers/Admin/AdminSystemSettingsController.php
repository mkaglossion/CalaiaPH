<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSystemAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AdminSystemSettingsController extends Controller
{
    public function index()
    {
        $settings = cache()->get('admin.settings', [
            'app_name' => config('app.name'),
            'app_timezone' => config('app.timezone'),
            'app_currency' => 'PHP',
            'business_hours_start' => '06:00',
            'business_hours_end' => '22:00',
            'enable_new_bookings' => true,
            'vat_rate' => 12,
            'system_maintenance' => false,
            'maintenance_message' => '',
        ]);
        return view('admin.settings.system', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_timezone' => 'required|timezone',
            'app_currency' => 'required|in:PHP,USD,EUR',
            'business_hours_start' => 'required|date_format:H:i',
            'business_hours_end' => 'required|date_format:H:i',
            'enable_new_bookings' => 'nullable|boolean',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'system_maintenance' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string|max:500',
        ]);

        // Ensure proper type conversions
        $validated['vat_rate'] = (float) $validated['vat_rate'];
        $validated['system_maintenance'] = (bool) ($request->has('system_maintenance') && $request->input('system_maintenance') != '0');
        $validated['enable_new_bookings'] = (bool) ($request->has('enable_new_bookings') && $request->input('enable_new_bookings') != '0');
        $validated['maintenance_message'] = trim($validated['maintenance_message'] ?? '');

        // Clear old cache and set new one
        cache()->forget('admin.settings');
        cache()->put('admin.settings', $validated, now()->addDays(365));
        
        // Log the setting change
        Log::info('Admin updated system settings', [
            'maintenance_mode' => $validated['system_maintenance'],
            'user_id' => Auth::id()
        ]);

        return back()->with('success', 'System settings updated successfully.');
    }

    public function showAlerts()
    {
        $unresolved = AdminSystemAlert::unresolved()
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.settings.alerts', compact('unresolved'));
    }

    public function resolveAlert($id)
    {
        $alert = AdminSystemAlert::findOrFail($id);
        $alert->resolve();

        return back()->with('success', 'Alert resolved.');
    }

    public function clearResolvedAlerts()
    {
        AdminSystemAlert::whereNotNull('resolved_at')
            ->where('updated_at', '<', now()->subDays(7))
            ->delete();

        return back()->with('success', 'Cleared old resolved alerts.');
    }

    public function backup()
    {
        Artisan::call('backup:run');
        return back()->with('success', 'Backup started. Check your notification for completion.');
    }
}
