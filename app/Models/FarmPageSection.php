<?php

namespace App\Models;

use App\Helpers\ImageHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class FarmPageSection extends Model
{
    protected $fillable = [
        'key',
        'title',
        'description',
        'image_url',
        'image_public_id',
        'secondary_image_url',
        'secondary_image_public_id',
        'items',
        'settings',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public static function ensureDefaults(): Collection
    {
        foreach (config('farm_page_sections.sections', []) as $key => $defaults) {
            static::firstOrCreate(
                ['key' => $key],
                static::defaultAttributes($key, $defaults)
            );
        }

        return static::query()->orderBy('sort_order')->get();
    }

    public static function publicSections(): Collection
    {
        $defaults = config('farm_page_sections.sections', []);
        $stored = collect();
        $tableAvailable = false;

        try {
            if (Schema::hasTable('farm_page_sections')) {
                $stored = static::query()->orderBy('sort_order')->get()->keyBy('key');
                $tableAvailable = true;
            }
        } catch (Throwable) {
            $tableAvailable = false;
        }

        return collect($defaults)
            ->map(function (array $sectionDefaults, string $key) use ($stored, $tableAvailable): ?self {
                $section = $stored->get($key);

                if ($tableAvailable && $section !== null) {
                    return $section->is_active ? $section : null;
                }

                return new static(static::defaultAttributes($key, $sectionDefaults));
            })
            ->filter()
            ->sortBy('sort_order')
            ->values();
    }

    public function adminPayload(): array
    {
        $items = collect($this->items ?? [])->map(function (array $item): array {
            $item['image_preview_url'] = filled($item['image_url'] ?? null)
                ? ImageHelper::src($item['image_url'])
                : ImageHelper::placeholder();

            return $item;
        })->values()->all();

        return [
            'id' => $this->id,
            'key' => $this->key,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'image_public_id' => $this->image_public_id,
            'image_preview_url' => filled($this->image_url) ? ImageHelper::src($this->image_url) : ImageHelper::placeholder(),
            'secondary_image_url' => $this->secondary_image_url,
            'secondary_image_public_id' => $this->secondary_image_public_id,
            'secondary_image_preview_url' => filled($this->secondary_image_url) ? ImageHelper::src($this->secondary_image_url) : ImageHelper::placeholder(),
            'items' => $items,
            'settings' => $this->settings ?? [],
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }

    public function cloudinaryPublicIds(): array
    {
        return collect([
            $this->image_public_id,
            $this->secondary_image_public_id,
            ...collect($this->items ?? [])->pluck('image_public_id')->all(),
        ])->filter(fn ($value) => filled($value))->unique()->values()->all();
    }

    private static function defaultAttributes(string $key, array $defaults): array
    {
        return [
            'key' => $key,
            'title' => $defaults['title'] ?? null,
            'description' => $defaults['description'] ?? null,
            'image_url' => $defaults['image_url'] ?? null,
            'image_public_id' => null,
            'secondary_image_url' => $defaults['secondary_image_url'] ?? null,
            'secondary_image_public_id' => null,
            'items' => $defaults['items'] ?? [],
            'settings' => $defaults['settings'] ?? [],
            'is_active' => true,
            'sort_order' => $defaults['sort_order'] ?? 0,
        ];
    }
}
