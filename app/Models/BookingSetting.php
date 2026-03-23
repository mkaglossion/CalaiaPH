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
     * For max_pets_per_room, returns integer; otherwise returns float
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        // For policy settings (max_pets_per_room), return as integer
        if ($setting->category === 'policy') {
            return (int)$setting->value;
        }

        // For pricing settings, return as float
        return (float)$setting->value;
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
