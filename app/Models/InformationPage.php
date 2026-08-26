<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class InformationPage extends Model
{
    public const DEFAULT_CONTACT_ADDRESS = 'Purok 3, Brgy. Bisaya, Calaca City, Batangas, Philippines';

    public const DEFAULT_CONTACT_PHONE = '+63 905 129 4741';

    public const DEFAULT_CONTACT_EMAIL = 'calaia@calaia.com';

    public const DEFAULT_CONTACT_HOURS = 'Open Daily 8:00 AM – 6:00 PM';

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
        'contact_address',
        'contact_phone',
        'contact_email',
        'contact_hours',
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
            return $this->replaceContactDetails((string) $this->body_html);
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

        return $this->replaceContactDetails(str_replace($applicationUrl, '/', $bodyHtml));
    }

    public function renderedBodyHtml(): string
    {
        $html = (string) $this->body_html;

        if ($this->slug !== 'contact') {
            return $html;
        }

        $html = str_replace(
            ['{{contact_form}}', '{{contact_map}}'],
            [
                view('pages.information.partials.contact-form')->render(),
                view('pages.information.partials.contact-map')->render(),
            ],
            $html
        );

        return $this->replaceContactDetails($html);
    }

    public function resolvedContactAddress(): string
    {
        $address = trim((string) $this->contact_address);

        return $address !== '' ? $address : self::DEFAULT_CONTACT_ADDRESS;
    }

    public function resolvedContactPhone(): string
    {
        $phone = trim((string) $this->contact_phone);

        return $phone !== '' ? $phone : self::DEFAULT_CONTACT_PHONE;
    }

    public function resolvedContactEmail(): string
    {
        $email = trim((string) $this->contact_email);

        return $email !== '' ? $email : self::DEFAULT_CONTACT_EMAIL;
    }

    public function resolvedContactHours(): string
    {
        return self::formatContactHours($this->resolvedContactHoursValue());
    }

    public function resolvedContactHoursValue(): string
    {
        return self::contactHoursValue((string) $this->contact_hours);
    }

    public static function contactHoursValue(?string $hours): string
    {
        $hours = trim((string) $hours);
        $hours = preg_replace('/^Open Daily\s*:?[\s\x{00A0}]*/iu', '', $hours) ?? '';

        if ($hours !== '') {
            return $hours;
        }

        return preg_replace('/^Open Daily\s*/iu', '', self::DEFAULT_CONTACT_HOURS) ?? '8:00 AM – 6:00 PM';
    }

    public static function formatContactHours(string $hours): string
    {
        return 'Open Daily '.self::contactHoursValue($hours);
    }

    /**
     * @return array{address: string, phone: string, phone_uri: string, email: string, email_uri: string, hours: string, hours_value: string}
     */
    public static function contactDetails(): array
    {
        $details = [
            'address' => self::DEFAULT_CONTACT_ADDRESS,
            'phone' => self::DEFAULT_CONTACT_PHONE,
            'email' => self::DEFAULT_CONTACT_EMAIL,
            'hours' => self::DEFAULT_CONTACT_HOURS,
        ];

        try {
            if (Schema::hasTable('information_pages')) {
                $columns = collect(['contact_address', 'contact_phone', 'contact_email', 'contact_hours'])
                    ->filter(static fn (string $column): bool => Schema::hasColumn('information_pages', $column))
                    ->values()
                    ->all();

                $page = $columns === []
                    ? null
                    : static::query()->where('slug', 'contact')->first($columns);

                if ($page !== null) {
                    $details['address'] = $page->resolvedContactAddress();
                    $details['phone'] = $page->resolvedContactPhone();
                    $details['email'] = $page->resolvedContactEmail();
                    $details['hours'] = $page->resolvedContactHours();
                }
            }
        } catch (Throwable) {
            // Use public defaults while a deployment is migrating.
        }

        return [
            ...$details,
            'phone_uri' => self::contactPhoneUri($details['phone']),
            'email_uri' => 'mailto:'.$details['email'],
            'hours_value' => self::contactHoursValue($details['hours']),
        ];
    }

    public static function contactPhone(): string
    {
        return static::contactDetails()['phone'];
    }

    public static function contactPhoneUri(?string $phone = null): string
    {
        $phone = trim((string) ($phone ?? static::contactPhone()));
        $normalized = preg_replace('/[^0-9+]/', '', $phone) ?? '';

        if (str_starts_with($normalized, '09')) {
            $normalized = '+63'.substr($normalized, 1);
        }

        return 'tel:'.$normalized;
    }

    public static function extractContactPhoneFromHtml(?string $html): ?string
    {
        if (!is_string($html) || trim($html) === '') {
            return null;
        }

        if (preg_match('/<a\b[^>]*\bhref\s*=\s*["\']tel:[^"\']*["\'][^>]*>(.*?)<\/a>/is', $html, $matches)) {
            $phone = html_entity_decode(strip_tags((string) $matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } else {
            // Visual editors may preserve the displayed number while removing
            // the tel link. Read the Philippine phone text as a fallback.
            $plainText = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $plainText = preg_replace('/\s+/', ' ', $plainText) ?? '';

            if (!preg_match('/(?:\+?63|0)[0-9\s().-]{8,20}/', $plainText, $matches)) {
                return null;
            }

            $phone = $matches[0];
        }

        $phone = preg_replace('/\s+/', ' ', trim($phone)) ?? '';

        if ($phone === '' || !preg_match('/^[0-9+().\-\s]+$/', $phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';

        return strlen($digits) >= 7 ? $phone : null;
    }

    public static function extractContactAddressFromHtml(?string $html): ?string
    {
        return self::extractLabeledValue($html, 'Address', 'address|p|span');
    }

    public static function extractContactEmailFromHtml(?string $html): ?string
    {
        if (!is_string($html) || trim($html) === '') {
            return null;
        }

        if (preg_match('/\bmailto:([^"\'?#\s>]+)/i', $html, $matches)) {
            $email = rawurldecode(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
        }

        $plainText = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $plainText, $matches)
            ? $matches[0]
            : null;
    }

    public static function extractContactHoursFromHtml(?string $html): ?string
    {
        $labeledHours = self::extractLabeledValue($html, 'Hours', 'p|span|time');

        if ($labeledHours !== null || !is_string($html)) {
            return $labeledHours;
        }

        if (preg_match(
            '/<(?:p|span|time)\b[^>]*>\s*Open Daily\s*<\/(?:p|span|time)>\s*<(?:p|span|time)\b[^>]*>(.*?)<\/(?:p|span|time)>/is',
            $html,
            $matches
        )) {
            $value = html_entity_decode(strip_tags((string) $matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

            return $value !== '' ? self::formatContactHours($value) : null;
        }

        if (!preg_match('/<(?:p|span|time)\b[^>]*>(\s*Open Daily\b.*?)<\/(?:p|span|time)>/is', $html, $matches)) {
            return null;
        }

        $hours = html_entity_decode(strip_tags((string) $matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $hours = preg_replace('/\s+/u', ' ', trim($hours)) ?? '';

        return $hours !== '' ? $hours : null;
    }

    private static function extractLabeledValue(
        ?string $html,
        string $label,
        string $valueTags
    ): ?string {
        if (!is_string($html) || trim($html) === '') {
            return null;
        }

        $pattern = '/<(?:span|p|strong|h[1-6])\b[^>]*>\s*'.preg_quote($label, '/').'\s*<\/(?:span|p|strong|h[1-6])>\s*<(?:'.$valueTags.')\b[^>]*>(.*?)<\/(?:'.$valueTags.')>/is';

        if (!preg_match($pattern, $html, $matches)) {
            return null;
        }

        $value = html_entity_decode(strip_tags((string) $matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        return $value !== '' ? $value : null;
    }

    private function replaceContactDetails(string $html): string
    {
        if ($this->slug !== 'contact' || $html === '') {
            return $html;
        }

        $address = $this->resolvedContactAddress();
        $phone = $this->resolvedContactPhone();
        $phoneUri = self::contactPhoneUri($phone);
        $email = $this->resolvedContactEmail();
        $hours = $this->resolvedContactHours();
        $hoursValue = $this->resolvedContactHoursValue();

        $html = $this->replaceLabeledValue($html, 'Address', 'address|p|span', $address);
        $html = $this->replaceLabeledValue($html, 'Phone', 'a|p|span', $phone, $phoneUri);
        $html = $this->replaceLabeledValue($html, 'Email', 'a|p|span', $email, 'mailto:'.$email);
        $html = $this->replaceLabeledValue($html, 'Hours', 'p|span|time', $hours);
        $html = preg_replace_callback(
            '/(<(p|span|time)\b[^>]*>)\s*Open Daily\s*(<\/\2>)(\s*)(<(p|span|time)\b[^>]*>)(.*?)(<\/\6>)/is',
            static fn (array $matches): string => $matches[5].'Open Daily '.e($hoursValue).$matches[8],
            $html
        ) ?? $html;
        $html = preg_replace_callback(
            '/(<(p|span|time)\b[^>]*>)(\s*Open Daily\b.*?)(<\/\2>)(?!\s*<(?:p|span|time)\b)/is',
            static fn (array $matches): string => $matches[1].'Open Daily '.e($hoursValue).$matches[4],
            $html
        ) ?? $html;
        $html = preg_replace_callback(
            '/<i\b([^>]*\bfa-clock\b[^>]*)>.*?Open Daily.*?<\/i>/is',
            static fn (array $matches): string => '<i'.$matches[1].'></i>'
                .'<span class="text-gray-600 font-normal" style="font-weight: 400 !important;">'
                .'Open Daily '.e($hoursValue).'</span>',
            $html
        ) ?? $html;

        return preg_replace_callback(
            '/(<a\b[^>]*\bhref\s*=\s*)(["\'])tel:[^"\']*\2([^>]*>)(.*?)(<\/a>)/is',
            static fn (array $matches): string => $matches[1]
                .$matches[2].e($phoneUri).$matches[2]
                .$matches[3].e($phone).$matches[5],
            $html
        ) ?? $html;
    }

    private function replaceLabeledValue(
        string $html,
        string $label,
        string $valueTags,
        string $value,
        ?string $href = null
    ): string {
        $pattern = '/(<(?:span|p|strong|h[1-6])\b[^>]*>\s*'.preg_quote($label, '/').'\s*<\/(?:span|p|strong|h[1-6])>\s*<('.$valueTags.')\b)([^>]*)(>)(.*?)(<\/\2>)/is';

        return preg_replace_callback($pattern, static function (array $matches) use ($value, $href): string {
            $attributes = $matches[3];

            if ($href !== null && strtolower($matches[2]) === 'a') {
                if (preg_match('/\bhref\s*=\s*(["\']).*?\1/is', $attributes)) {
                    $attributes = preg_replace('/\bhref\s*=\s*(["\']).*?\1/is', 'href="'.e($href).'"', $attributes) ?? $attributes;
                } else {
                    $attributes .= ' href="'.e($href).'"';
                }
            }

            return $matches[1].$attributes.$matches[4].e($value).$matches[6];
        }, $html) ?? $html;
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

    public static function isVisibleInNavigation(string $slug): bool
    {
        return (bool) config("information_pages.pages.{$slug}.show_in_navigation", true);
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
            if (! static::isVisibleInNavigation($slug)) {
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
        return [
            'slug' => $slug,
            'nav_label' => $defaults['nav_label'],
            'meta_title' => $defaults['meta_title'],
            'meta_description' => $defaults['meta_description'],
            'meta_keywords' => $defaults['meta_keywords'],
            'hero_eyebrow' => $defaults['hero_eyebrow'],
            'hero_title' => $defaults['hero_title'],
            'hero_description' => $defaults['hero_description'],
            'contact_address' => $slug === 'contact'
                ? ($defaults['contact_address'] ?? self::DEFAULT_CONTACT_ADDRESS)
                : null,
            'contact_phone' => $slug === 'contact'
                ? ($defaults['contact_phone'] ?? self::DEFAULT_CONTACT_PHONE)
                : null,
            'contact_email' => $slug === 'contact'
                ? ($defaults['contact_email'] ?? self::DEFAULT_CONTACT_EMAIL)
                : null,
            'contact_hours' => $slug === 'contact'
                ? ($defaults['contact_hours'] ?? self::DEFAULT_CONTACT_HOURS)
                : null,
            'body_html' => null,
            'is_published' => true,
            'sort_order' => $defaults['sort_order'],
        ];
    }
}
