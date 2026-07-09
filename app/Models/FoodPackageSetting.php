<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodPackageSetting extends Model
{
    protected $table = 'food_package_settings';

    protected $fillable = ['key', 'label', 'value', 'description'];

    private const PACKAGE_CATEGORY_LABEL_DEFINITIONS = [
        'messhall' => [
            'setting_key' => 'messhall_label',
            'label' => 'Messhall Label',
            'description' => 'Shown in booking filters, package cards, and admin category choices.',
            'default' => 'Messhall',
        ],
        'overnight_stay_menu' => [
            'setting_key' => 'overnight_stay_menu_label',
            'label' => 'Overnight Stay Menu Label',
            'description' => 'Shown for the overnight stay menu package category in booking and admin.',
            'default' => 'Messhall',
        ],
        'cook_your_own' => [
            'setting_key' => 'cook_your_own_label',
            'label' => 'Cook Your Own Label',
            'description' => 'Shown in booking filters, package cards, and admin category choices.',
            'default' => 'Cook Your Own',
        ],
        'special' => [
            'setting_key' => 'special_label',
            'label' => 'Special Label',
            'description' => 'Shown in booking filters, package cards, and admin category choices.',
            'default' => 'Special',
        ],
    ];

    private const FOOD_SECTION_COPY_DEFINITIONS = [
        'messhall' => [
            'heading_key' => 'messhall_section_heading',
            'description_key' => 'messhall_section_description',
            'label' => 'Messhall Section',
            'default_heading' => 'Messhall Items',
            'default_description' => 'Choose from messhall items below.',
        ],
        'overnight_stay_menu' => [
            'heading_key' => 'overnight_stay_menu_section_heading',
            'description_key' => 'overnight_stay_menu_section_description',
            'label' => 'Overnight Stay Menu Section',
            'default_heading' => 'Overnight Stay Menu Packages',
            'default_description' => 'Choose from overnight stay menu packages below.',
        ],
        'cook_your_own' => [
            'heading_key' => 'cook_your_own_section_heading',
            'description_key' => 'cook_your_own_section_description',
            'label' => 'Cook Your Own Section',
            'default_heading' => 'Cook Your Own Packages',
            'default_description' => 'Choose from Cook Your Own packages below.',
        ],
        'special' => [
            'heading_key' => 'special_section_heading',
            'description_key' => 'special_section_description',
            'label' => 'Special Section',
            'default_heading' => 'Special Packages',
            'default_description' => 'Choose from Special packages below.',
        ],
        'breakfast' => [
            'heading_key' => 'breakfast_section_heading',
            'description_key' => 'breakfast_section_description',
            'label' => 'Breakfast Section',
            'default_heading' => 'Breakfast',
            'default_description' => 'Choose your breakfast options below.',
        ],
        'lunch' => [
            'heading_key' => 'lunch_section_heading',
            'description_key' => 'lunch_section_description',
            'label' => 'Lunch Section',
            'default_heading' => 'Lunch',
            'default_description' => 'Choose your lunch options below.',
        ],
        'dinner' => [
            'heading_key' => 'dinner_section_heading',
            'description_key' => 'dinner_section_description',
            'label' => 'Dinner Section',
            'default_heading' => 'Dinner',
            'default_description' => 'Choose your dinner options below.',
        ],
        'all_day' => [
            'heading_key' => 'all_day_section_heading',
            'description_key' => 'all_day_section_description',
            'label' => 'All Day Section',
            'default_heading' => 'All Day',
            'default_description' => 'Choose your all day options below.',
        ],
    ];

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

    public static function getPackageCategoryLabelDefinitions(): array
    {
        return self::PACKAGE_CATEGORY_LABEL_DEFINITIONS;
    }

    public static function getPackageCategoryLabels(): array
    {
        $labels = [];

        foreach (self::PACKAGE_CATEGORY_LABEL_DEFINITIONS as $category => $definition) {
            $labels[$category] = self::getValue($definition['setting_key'], $definition['default']);
        }

        return $labels;
    }

    public static function getFoodBookingSectionDefinitions(): array
    {
        return self::FOOD_SECTION_COPY_DEFINITIONS;
    }

    public static function getFoodBookingSectionCopy(): array
    {
        $copy = [];

        foreach (self::FOOD_SECTION_COPY_DEFINITIONS as $section => $definition) {
            $copy[$section] = [
                'heading' => self::getValue($definition['heading_key'], $definition['default_heading']),
                'description' => self::getValue($definition['description_key'], $definition['default_description']),
            ];
        }

        return $copy;
    }
}
