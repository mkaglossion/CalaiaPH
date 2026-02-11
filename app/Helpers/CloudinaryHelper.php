<?php

namespace App\Helpers;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class CloudinaryHelper
{
    /**
     * Generate optimized Cloudinary URL with automatic quality and format
     */
    public static function optimize(?string $url, int $width = 1920, bool $sharpen = false): ?string
    {
        if (!$url || !is_string($url)) {
            return $url;
        }

        // If not Cloudinary URL, return as is
        if (!str_contains($url, 'res.cloudinary.com')) {
            return $url;
        }

        // Build transformation string
        $transformations = "w_{$width},q_auto:best,f_auto";
        
        if ($sharpen) {
            $transformations .= ',e_sharpen:100';
        }

        // Replace upload/ with transformations
        return preg_replace('/\/upload\//', "/upload/{$transformations}/", $url, 1);
    }

    /**
     * Generate srcset for responsive images
     */
    public static function srcset(?string $url, array $widths = [640, 768, 1024, 1280, 1920, 2500]): ?string
    {
        if (!$url || !is_string($url)) {
            return null;
        }

        if (!str_contains($url, 'res.cloudinary.com')) {
            return null;
        }

        $srcset = [];
        foreach ($widths as $width) {
            $srcset[] = self::optimize($url, $width) . " {$width}w";
        }

        return implode(', ', $srcset);
    }

    /**
     * Get optimized URL for swiper slides (large images)
     */
    public static function swiperSlide(?string $url): ?string
    {
        return self::optimize($url, 1920, true);
    }

    /**
     * Get optimized URL for thumbnail images
     */
    public static function thumbnail(?string $url, int $size = 300): ?string
    {
        return self::optimize($url, $size);
    }

    /**
     * Get optimized URL for cards/featured images
     */
    public static function card(?string $url): ?string
    {
        return self::optimize($url, 1280, true);
    }
}
