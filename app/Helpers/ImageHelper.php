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

        if (!preg_match('#^https?://#i', $path)) {
            if (str_starts_with($path, '/')) {
                return url($path);
            }

            return asset(ltrim($path, '/'));
        }

        if (self::isVideo($path)) {
            return $path;
        }

        if (str_contains($path, 'res.cloudinary.com')) {
            return CloudinaryHelper::card($path) ?? $path;
        }

        return $path;
    }

    public static function isVideo(?string $path): bool
    {
        if (!is_string($path)) {
            return false;
        }
        $path = trim($path);
        if (str_contains($path, '/video/')) {
            return true;
        }
        if (str_contains($path, 'res.cloudinary.com') && preg_match('#/video/upload/#', $path)) {
            return true;
        }
        if (preg_match('#\.(mp4|webm|ogg|mov|avi|wmv)$#i', $path)) {
            return true;
        }
        return false;
    }

    public static function placeholder(): string
    {
        return self::PLACEHOLDER;
    }

    public static function videoThumbnail(?string $path): ?string
    {
        if (!is_string($path)) {
            return $path;
        }

        $path = trim($path);

        if (str_contains($path, 'res.cloudinary.com') && preg_match('#/video/upload/#', $path)) {
            // Extract a still frame from the video at offset 0 and deliver it as a JPG.
            // Keeping the "video/upload" resource type (not "image/upload") avoids 404s
            // and is the reliable Cloudinary way to get a video poster frame.
            $thumbnail = preg_replace('#/video/upload/#', '/video/upload/so_0,w_1280/', $path);

            return preg_replace('#\.(mp4|webm|ogg|mov|avi|wmv)(\?.*)?$#i', '.jpg', $thumbnail) ?: $thumbnail;
        }

        return $path;
    }
}
