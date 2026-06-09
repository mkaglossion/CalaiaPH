<?php

namespace App\Helpers;

class ImageHelper
{
    private const PLACEHOLDER = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="600" height="400"%3E%3Crect fill="%23e5e7eb" width="600" height="400"/%3E%3Ctext x="50%25" y="50%25" font-family="sans-serif" font-size="24" fill="%239ca3af" text-anchor="middle" dy=".3em"%3ENo Image Available%3C/text%3E%3C/svg%3E';

    public static function src(?string $path): string
    {
        $path = is_string($path) ? trim($path) : '';

        if ($path === '') {
            return self::placeholder();
        }

        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        if (str_starts_with($path, '//')) {
            return 'https:' . $path;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return url($path);
        }

        return asset(ltrim($path, '/'));
    }

    public static function placeholder(): string
    {
        return self::PLACEHOLDER;
    }
}
