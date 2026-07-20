<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSystemAlert;
use App\Models\SystemSetting;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AdminSystemSettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::cachedData();
        return view('admin.settings.system', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $current = SystemSetting::cachedData();
        $input = array_merge($current, $request->all());
        foreach (['footer_instagram_url', 'footer_facebook_url', 'footer_messenger_url', 'footer_email'] as $nullableKey) {
            if (($input[$nullableKey] ?? null) === '') {
                $input[$nullableKey] = null;
            }
        }

        $validated = validator($input, [
            'app_name' => 'required|string|max:255',
            'app_timezone' => 'required|timezone',
            'app_currency' => 'required|in:PHP,USD,EUR',
            'business_hours_start' => 'required|date_format:H:i',
            'business_hours_end' => 'required|date_format:H:i',
            'enable_new_bookings' => 'nullable|boolean',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'system_maintenance' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string|max:500',
            'footer_title' => 'nullable|string|max:255',
            'footer_description' => 'nullable|string|max:300',
            'footer_social_heading' => 'nullable|string|max:160',
            'footer_instagram_url' => 'nullable|url:http,https|max:2048',
            'footer_facebook_url' => 'nullable|url:http,https|max:2048',
            'footer_messenger_url' => 'nullable|url:http,https|max:2048',
            'footer_email' => 'nullable|email:rfc|max:255',
            'logo_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg|max:10240',
            'remove_logo' => 'nullable|in:1',
            'favicon_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg,ico|max:2048',
            'remove_favicon' => 'nullable|in:1',
        ])->validate();

        $stringSetting = function (string $key) use ($request, $validated, $current): string {
            if ($request->exists($key)) {
                return trim((string) ($validated[$key] ?? ''));
            }

            return trim((string) ($current[$key] ?? ''));
        };

        $setting = SystemSetting::current() ?? new SystemSetting();
        $setting->fill([
            'app_name' => $validated['app_name'],
            'app_timezone' => $validated['app_timezone'],
            'app_currency' => $validated['app_currency'],
            'business_hours_start' => $validated['business_hours_start'],
            'business_hours_end' => $validated['business_hours_end'],
            'enable_new_bookings' => $request->has('enable_new_bookings')
                ? $request->boolean('enable_new_bookings')
                : (bool) ($current['enable_new_bookings'] ?? true),
            'vat_rate' => (float) $validated['vat_rate'],
            'system_maintenance' => $request->has('system_maintenance')
                ? $request->boolean('system_maintenance')
                : (bool) ($current['system_maintenance'] ?? false),
            'maintenance_message' => trim((string) ($validated['maintenance_message'] ?? '')),
            'footer_title' => $stringSetting('footer_title'),
            'footer_description' => $stringSetting('footer_description'),
            'footer_social_heading' => $stringSetting('footer_social_heading'),
            'footer_instagram_url' => $stringSetting('footer_instagram_url'),
            'footer_facebook_url' => $stringSetting('footer_facebook_url'),
            'footer_messenger_url' => $stringSetting('footer_messenger_url'),
            'footer_email' => $stringSetting('footer_email'),
        ]);

        if ($request->boolean('remove_logo')) {
            $this->destroyUpload($setting->logo_public_id ?? null);
            $setting->logo_url = null;
            $setting->logo_public_id = null;
        } elseif ($request->hasFile('logo_file')) {
            $this->destroyUpload($setting->logo_public_id ?? null);
            $upload = $this->storeUpload($request->file('logo_file'), 'system/logo');
            $setting->logo_url = $upload['url'] ?? null;
            $setting->logo_public_id = $upload['public_id'] ?? null;
        }

        if ($request->boolean('remove_favicon')) {
            $this->destroyUpload($setting->favicon_public_id ?? null);
            $setting->favicon_url = null;
            $setting->favicon_public_id = null;
        } elseif ($request->hasFile('favicon_file')) {
            $this->destroyUpload($setting->favicon_public_id ?? null);
            $upload = $this->storeUpload($request->file('favicon_file'), 'system/favicon');
            $setting->favicon_url = $upload['url'] ?? null;
            $setting->favicon_public_id = $upload['public_id'] ?? null;
        }

        $setting->save();

        SystemSetting::refreshCache();
        
        // Log the setting change
        Log::info('Admin updated system settings', [
            'maintenance_mode' => $setting->system_maintenance,
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

    private function storeUpload(?UploadedFile $file, string $folder = 'system'): array
    {
        if (!$file) {
            return [];
        }

        try {
            $uploadResult = Cloudinary::uploadApi()->upload($file->getRealPath(), [
                'resource_type' => 'image',
                'folder' => $folder,
            ]);

            return [
                'url' => $uploadResult['secure_url'] ?? null,
                'public_id' => $uploadResult['public_id'] ?? null,
            ];
        } catch (Throwable $e) {
            report($e);
            throw $e;
        }
    }

    private function destroyUpload(?string $publicId): void
    {
        if (empty($publicId)) {
            return;
        }

        try {
            Cloudinary::uploadApi()->destroy($publicId, [
                'resource_type' => 'image',
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    
}
