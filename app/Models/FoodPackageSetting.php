<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodPackageSetting extends Model
{
    protected $table = 'food_package_settings';

    protected $fillable = ['key', 'label', 'value', 'description'];

    /**
     * Get a setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting?->value ?? $default;
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllAsArray()
    {
        return self::get()->pluck('value', 'key')->toArray();
    }
}
