<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingSetting extends Model
{
    protected $fillable = ['key', 'label', 'value', 'description', 'category'];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? (float)$setting->value : $default;
    }

    /**
     * Get all settings grouped by category
     */
    public static function getByCategory($category)
    {
        return self::where('category', $category)->get();
    }

    /**
     * Cache key for all settings
     */
    public static function getCacheKey()
    {
        return 'booking_settings_all';
    }
}
