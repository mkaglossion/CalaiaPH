<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class InformationPage extends Model
{
    private const BODY_START_MARKER = '<!-- INFORMATION_PAGE_BODY_START -->';

    private const BODY_END_MARKER = '<!-- INFORMATION_PAGE_BODY_END -->';

    protected $fillable = [
        'slug',
        'nav_label',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'hero_eyebrow',
        'hero_title',
        'hero_description',
        'body_html',
        'is_published',
        'sort_order',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function hasStoredBody(): bool
    {
        return is_string($this->body_html) && trim($this->body_html) !== '';
    }

    public function editableBodyHtml(): string
    {
        if ($this->hasStoredBody()) {
            return (string) $this->body_html;
        }

        $defaultView = config("information_pages.pages.{$this->slug}.default_view");

        if (! is_string($defaultView) || ! view()->exists($defaultView)) {
            return '';
        }

        $renderedPage = view($defaultView, ['informationPage' => $this])->render();
        $start = strpos($renderedPage, self::BODY_START_MARKER);
        $end = strpos($renderedPage, self::BODY_END_MARKER);

        if ($start === false || $end === false || $end <= $start) {
            return '';
        }

        $start += strlen(self::BODY_START_MARKER);
        $bodyHtml = trim(substr($renderedPage, $start, $end - $start));

        $bodyHtml = preg_replace(
            '/<!-- INFORMATION_CONTACT_FORM_START -->.*?<!-- INFORMATION_CONTACT_FORM_END -->/s',
            '{{contact_form}}',
            $bodyHtml
        ) ?? $bodyHtml;
        $bodyHtml = preg_replace(
            '/<!-- INFORMATION_CONTACT_MAP_START -->.*?<!-- INFORMATION_CONTACT_MAP_END -->/s',
            '{{contact_map}}',
            $bodyHtml
        ) ?? $bodyHtml;

        $applicationUrl = rtrim(url('/'), '/').'/';

        return str_replace($applicationUrl, '/', $bodyHtml);
    }

    public function renderedBodyHtml(): string
    {
        $html = (string) $this->body_html;

        if ($this->slug !== 'contact') {
            return $html;
        }

        return str_replace(
            ['{{contact_form}}', '{{contact_map}}'],
            [
                view('pages.information.partials.contact-form')->render(),
                view('pages.information.partials.contact-map')->render(),
            ],
            $html
        );
    }

    public static function ensureDefaults(): Collection
    {
        foreach (config('information_pages.pages', []) as $slug => $defaults) {
            static::firstOrCreate(
                ['slug' => $slug],
                static::defaultAttributes($slug, $defaults)
            );
        }

        return static::query()->with('updatedBy')->orderBy('sort_order')->get();
    }

    public static function findPublic(string $slug): ?self
    {
        $defaults = config("information_pages.pages.$slug");

        if (! is_array($defaults)) {
            return null;
        }

        try {
            if (Schema::hasTable('information_pages')) {
                $page = static::query()->where('slug', $slug)->first();

                if ($page !== null) {
                    return $page->is_published ? $page : null;
                }
            }
        } catch (Throwable) {
            // Keep public information pages available while a deployment is migrating.
        }

        return new static(static::defaultAttributes($slug, $defaults));
    }

    public static function navigationItems(): array
    {
        return static::resolvedNavigationItems();
    }

    public static function adminNavigationItems(): array
    {
        return array_map(
            static fn (array $item): array => [
                'label' => $item['label'],
                'route' => 'admin.information.index',
                'params' => ['page' => $item['slug']],
            ],
            static::resolvedNavigationItems(includeUnpublished: true)
        );
    }

    private static function resolvedNavigationItems(bool $includeUnpublished = false): array
    {
        $defaults = config('information_pages.pages', []);
        $stored = collect();
        $tableAvailable = false;

        try {
            if (Schema::hasTable('information_pages')) {
                $stored = static::query()->orderBy('sort_order')->get()->keyBy('slug');
                $tableAvailable = true;
            }
        } catch (Throwable) {
            $tableAvailable = false;
        }

        $items = [];

        foreach ($defaults as $slug => $pageDefaults) {
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
        return [
            'slug' => $slug,
            'nav_label' => $defaults['nav_label'],
            'meta_title' => $defaults['meta_title'],
            'meta_description' => $defaults['meta_description'],
            'meta_keywords' => $defaults['meta_keywords'],
            'hero_eyebrow' => $defaults['hero_eyebrow'],
            'hero_title' => $defaults['hero_title'],
            'hero_description' => $defaults['hero_description'],
            'body_html' => null,
            'is_published' => true,
            'sort_order' => $defaults['sort_order'],
        ];
    }
}
