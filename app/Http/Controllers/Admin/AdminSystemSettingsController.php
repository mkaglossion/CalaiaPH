<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSystemAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
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
            'vat_rate' => 0,
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

    public function showAlerts(Request $request)
    {
        $status = $request->string('status', 'unresolved')->toString();
        $severity = $request->string('severity', 'all')->toString();

        if (!in_array($status, ['unresolved', 'resolved', 'all'], true)) {
            $status = 'unresolved';
        }

        if (!in_array($severity, ['all', 'critical', 'warning', 'info'], true)) {
            $severity = 'all';
        }

        $alertsQuery = AdminSystemAlert::query()
            ->when($status === 'unresolved', fn ($q) => $q->whereNull('resolved_at'))
            ->when($status === 'resolved', fn ($q) => $q->whereNotNull('resolved_at'))
            ->when($severity !== 'all', fn ($q) => $q->where('severity', $severity))
            ->orderBy('severity', 'desc')
            ->orderByDesc('created_at');

        $alerts = $alertsQuery->paginate(15)->withQueryString();
        $severityBase = AdminSystemAlert::query()
            ->when($status === 'unresolved', fn ($q) => $q->whereNull('resolved_at'))
            ->when($status === 'resolved', fn ($q) => $q->whereNotNull('resolved_at'));
        $counts = [
            'all' => AdminSystemAlert::count(),
            'unresolved' => AdminSystemAlert::whereNull('resolved_at')->count(),
            'resolved' => AdminSystemAlert::whereNotNull('resolved_at')->count(),
            'critical' => (clone $severityBase)->where('severity', 'critical')->count(),
            'warning' => (clone $severityBase)->where('severity', 'warning')->count(),
            'info' => (clone $severityBase)->where('severity', 'info')->count(),
        ];

        return view('admin.settings.alerts', compact('alerts', 'counts', 'status', 'severity'));
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

    
}
