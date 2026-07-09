<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ActivityPackageSetting extends Model
{
    protected $table = 'activity_package_settings';

    protected $fillable = ['key', 'label', 'value', 'description'];

    private const CATEGORY_LABEL_DEFINITIONS = [
        'adventure' => [
            'setting_key' => 'adventure_label',
            'label' => 'Adventure Label',
            'description' => 'Shown in activity filters, cards, and admin category choices.',
            'default' => 'Adventure',
        ],
        'sports' => [
            'setting_key' => 'sports_label',
            'label' => 'Sports & Games Label',
            'description' => 'Shown in activity filters, cards, and admin category choices.',
            'default' => 'Sports & Games',
        ],
        'rrcamp' => [
            'setting_key' => 'rrcamp_label',
            'label' => 'R & R & Camp Label',
            'description' => 'Shown in activity filters, cards, and admin category choices.',
            'default' => 'R & R & Camp',
        ],
        'farm' => [
            'setting_key' => 'farm_label',
            'label' => 'Farm Label',
            'description' => 'Shown in activity filters, cards, and admin category choices.',
            'default' => 'Farm',
        ],
        'special' => [
            'setting_key' => 'special_label',
            'label' => 'Special Label',
            'description' => 'Shown in activity package cards and admin category choices.',
            'default' => 'Special',
        ],
    ];

    private const BOOKING_SECTION_COPY_DEFINITIONS = [
        'packages' => [
            'heading_key' => 'packages_section_heading',
            'description_key' => 'packages_section_description',
            'label' => 'Activity Packages Section',
            'default_heading' => 'Activity Packages',
            'default_description' => 'Curated activity bundles with special pricing',
        ],
        'adventure' => [
            'heading_key' => 'adventure_section_heading',
            'description_key' => 'adventure_section_description',
            'label' => 'Adventure Section',
            'default_heading' => 'Adventure',
            'default_description' => 'Choose from activities a la carte below.',
        ],
        'sports' => [
            'heading_key' => 'sports_section_heading',
            'description_key' => 'sports_section_description',
            'label' => 'Sports & Games Section',
            'default_heading' => 'Sports & Games',
            'default_description' => 'Choose from activities a la carte below.',
        ],
        'rrcamp' => [
            'heading_key' => 'rrcamp_section_heading',
            'description_key' => 'rrcamp_section_description',
            'label' => 'R & R & Camp Section',
            'default_heading' => 'R & R & Camp',
            'default_description' => 'Choose from activities a la carte below.',
        ],
        'farm' => [
            'heading_key' => 'farm_section_heading',
            'description_key' => 'farm_section_description',
            'label' => 'Farm Section',
            'default_heading' => 'Farm',
            'default_description' => 'Choose from activities a la carte below.',
        ],
        'special' => [
            'heading_key' => 'special_section_heading',
            'description_key' => 'special_section_description',
            'label' => 'Special Section',
            'default_heading' => 'Special',
            'default_description' => 'Choose from special activities below.',
        ],
    ];

    public static function getValue($key, $default = null)
    {
        if (!self::settingsTableExists()) {
            return $default;
        }

        $setting = self::where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    public static function getAllAsArray(): array
    {
        if (!self::settingsTableExists()) {
            return [];
        }

        return self::query()->pluck('value', 'key')->toArray();
    }

    public static function getCategoryLabelDefinitions(): array
    {
        return self::CATEGORY_LABEL_DEFINITIONS;
    }

    public static function getCategoryLabels(): array
    {
        $labels = [];

        foreach (self::CATEGORY_LABEL_DEFINITIONS as $category => $definition) {
            $labels[$category] = self::getValue($definition['setting_key'], $definition['default']);
        }

        return $labels;
    }

    public static function getCategoryLabel(?string $category, ?string $default = null): string
    {
        $key = strtolower(trim((string) $category));
        $labels = self::getCategoryLabels();

        if (isset($labels[$key]) && trim((string) $labels[$key]) !== '') {
            return $labels[$key];
        }

        if ($default !== null && trim($default) !== '') {
            return $default;
        }

        return ucfirst(str_replace('_', ' ', $key ?: 'special'));
    }

    public static function getBookingSectionDefinitions(): array
    {
        return self::BOOKING_SECTION_COPY_DEFINITIONS;
    }

    public static function getBookingSectionCopy(): array
    {
        $copy = [];

        foreach (self::BOOKING_SECTION_COPY_DEFINITIONS as $section => $definition) {
            $copy[$section] = [
                'heading' => self::getValue($definition['heading_key'], $definition['default_heading']),
                'description' => self::getValue($definition['description_key'], $definition['default_description']),
            ];
        }

        return $copy;
    }

    private static function settingsTableExists(): bool
    {
        static $exists = null;

        if ($exists !== null) {
            return $exists;
        }

        $exists = Schema::hasTable((new self())->getTable());

        return $exists;
    }
}
