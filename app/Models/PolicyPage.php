<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PolicyPage extends Model
{
    protected $fillable = [
        'slug',
        'nav_label',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'hero_eyebrow',
        'hero_title',
        'hero_description',
        'last_updated',
        'body_html',
        'is_published',
        'sort_order',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'last_updated' => 'date',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function renderedBodyHtml(): string
    {
        $lastUpdated = $this->last_updated?->format('F j, Y') ?? '';

        return str_replace('{{last_updated}}', e($lastUpdated), (string) $this->body_html);
    }

    public static function ensureDefaults(): Collection
    {
        foreach (config('policy_pages.pages', []) as $slug => $defaults) {
            static::firstOrCreate(
                ['slug' => $slug],
                static::defaultAttributes($slug, $defaults)
            );
        }

        return static::query()->with('updatedBy')->orderBy('sort_order')->get();
    }

    public static function findPublic(string $slug): ?self
    {
        $defaults = config("policy_pages.pages.$slug");

        if (! is_array($defaults)) {
            return null;
        }

        try {
            if (Schema::hasTable('policy_pages')) {
                $page = static::query()->where('slug', $slug)->first();

                if ($page !== null) {
                    return $page->is_published ? $page : null;
                }
            }
        } catch (Throwable) {
            // Keep public legal pages available while a deployment is migrating.
        }

        return new static(static::defaultAttributes($slug, $defaults));
    }

    public static function navigationItems(): array
    {
        return static::resolvedNavigationItems(respectNavigationVisibility: true);
    }

    public static function adminNavigationItems(): array
    {
        return array_map(
            static fn (array $item): array => [
                'label' => $item['label'],
                'route' => 'admin.policies.index',
                'params' => ['page' => $item['slug']],
            ],
            static::resolvedNavigationItems(
                includeUnpublished: true,
                respectNavigationVisibility: true
            )
        );
    }

    public static function isVisibleInNavigation(string $slug): bool
    {
        return (bool) config("policy_pages.pages.{$slug}.show_in_navigation", true);
    }

    private static function resolvedNavigationItems(
        bool $includeUnpublished = false,
        bool $respectNavigationVisibility = false
    ): array
    {
        $defaults = config('policy_pages.pages', []);
        $stored = collect();
        $tableAvailable = false;

        try {
            if (Schema::hasTable('policy_pages')) {
                $stored = static::query()->orderBy('sort_order')->get()->keyBy('slug');
                $tableAvailable = true;
            }
        } catch (Throwable) {
            $tableAvailable = false;
        }

        $items = [];

        foreach ($defaults as $slug => $pageDefaults) {
            if ($respectNavigationVisibility && ! static::isVisibleInNavigation($slug)) {
                continue;
            }

            $page = $stored->get($slug);

            if ($tableAvailable && $page !== null && ! $page->is_published && ! $includeUnpublished) {
                continue;
            }

            $items[] = [
                'slug' => $slug,
                'route' => $pageDefaults['route'],
                'label' => $page?->nav_label ?: $pageDefaults['nav_label'],
                'sort_order' => $page?->sort_order ?? $pageDefaults['sort_order'],
            ];
        }

        usort($items, fn (array $left, array $right) => $left['sort_order'] <=> $right['sort_order']);

        return $items;
    }

    private static function defaultAttributes(string $slug, array $defaults): array
    {
        $contentPath = resource_path($defaults['default_content']);

        return [
            'slug' => $slug,
            'nav_label' => $defaults['nav_label'],
            'meta_title' => $defaults['meta_title'],
            'meta_description' => $defaults['meta_description'],
            'meta_keywords' => $defaults['meta_keywords'],
            'hero_eyebrow' => $defaults['hero_eyebrow'],
            'hero_title' => $defaults['hero_title'],
            'hero_description' => $defaults['hero_description'],
            'last_updated' => $defaults['last_updated'],
            'body_html' => File::exists($contentPath) ? File::get($contentPath) : '',
            'is_published' => true,
            'sort_order' => $defaults['sort_order'],
        ];
    }
}
