<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SystemSetting extends Model
{
    protected $fillable = [
        'app_name',
        'app_timezone',
        'app_currency',
        'business_hours_start',
        'business_hours_end',
        'enable_new_bookings',
        'vat_rate',
        'system_maintenance',
        'maintenance_message',
        'logo_url',
        'logo_public_id',
        'favicon_url',
        'favicon_public_id',
        'footer_title',
        'footer_description',
    ];

    protected $casts = [
        'enable_new_bookings' => 'boolean',
        'system_maintenance' => 'boolean',
        'vat_rate' => 'float',
    ];

    public static function cacheKey(): string
    {
        return 'admin.settings';
    }

    public static function defaults(): array
    {
        return [
            'app_name' => config('app.name'),
            'app_timezone' => config('app.timezone'),
            'app_currency' => 'PHP',
            'business_hours_start' => '06:00',
            'business_hours_end' => '22:00',
            'enable_new_bookings' => true,
            'vat_rate' => 0,
            'system_maintenance' => false,
            'maintenance_message' => '',
            'logo_url' => null,
            'logo_public_id' => null,
            'favicon_url' => null,
            'favicon_public_id' => null,
            'footer_title' => 'CALAIA FARM',
            'footer_description' => "Your perfect getaway destination for relaxation and adventure.\nExperience nature at its finest in Calaca, Batangas.",
        ];
    }

    public static function current(): ?self
    {
        if (!Schema::hasTable('system_settings')) {
            return null;
        }

        return static::query()->first();
    }

    public static function currentData(): array
    {
        $settings = static::current();

        if (!$settings) {
            return static::defaults();
        }

        return array_merge(static::defaults(), $settings->only(array_keys(static::defaults())));
    }

    public static function cachedData(): array
    {
        if (!Schema::hasTable('system_settings')) {
            return static::defaults();
        }

        $cached = Cache::get(static::cacheKey());
        if (is_array($cached)) {
            $normalized = array_merge(static::defaults(), $cached);
            if ($normalized !== $cached) {
                Cache::forever(static::cacheKey(), $normalized);
            }

            return $normalized;
        }

        $data = static::currentData();
        Cache::forever(static::cacheKey(), $data);

        return $data;
    }

    public static function refreshCache(): array
    {
        Cache::forget(static::cacheKey());
        return static::cachedData();
    }
}
