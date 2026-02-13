<?php

namespace App\Helpers;

class CloudinaryHelper
{
    /**
     * Insert/replace Cloudinary transformations safely.
     *
     * Examples:
     *  - https://res.cloudinary.com/<cloud>/image/upload/v123/file.jpg
     *  - https://res.cloudinary.com/<cloud>/image/upload/w_800,q_auto/v123/file.jpg
     *
     * This will ensure transformations become:
     *  .../upload/<TRANSFORMS>/v123/file.jpg
     */
    private static function injectTransforms(string $url, string $transforms): string
    {
        // If URL already has transforms: /upload/<something>/...
        // Replace the <something> with our transforms.
        $patternWithTransforms = '#/upload/([^/]+)/#';

        if (preg_match($patternWithTransforms, $url)) {
            return preg_replace($patternWithTransforms, "/upload/{$transforms}/", $url, 1);
        }

        // Otherwise insert transforms right after /upload/
        return preg_replace('#/upload/#', "/upload/{$transforms}/", $url, 1);
    }

    private static function isCloudinary(?string $url): bool
    {
        return is_string($url) && str_contains($url, 'res.cloudinary.com') && str_contains($url, '/upload/');
    }

    /**
     * Generate an optimized Cloudinary URL
     *
     * @param string|null $url
     * @param int $width          Target width
     * @param bool $sharpen       Add sharpening (recommended for large/hero images)
     * @param bool $cover         If true, crop to fill (best for object-cover UI)
     * @param string $quality     'good' (recommended), 'best' (heavier), 'eco' (smaller)
     */
    public static function optimize(
        ?string $url,
        int $width = 1920,
        bool $sharpen = false,
        bool $cover = true,
        string $quality = 'good'
    ): ?string {
        if (!$url || !is_string($url)) return $url;
        if (!self::isCloudinary($url)) return $url;

        // Clamp a bit (avoid weird inputs)
        $width = max(50, min($width, 5000));

        // ✅ Key clarity settings:
        // - f_auto: best format (webp/avif where supported)
        // - q_auto:<mode>: balanced quality
        // - dpr_auto: crisp on retina / high-DPI
        // - w_<width>: correct sizing so browser doesn't stretch
        $transforms = [
            "f_auto",
            "q_auto:{$quality}",
            "dpr_auto",
            "w_{$width}",
        ];

        // If your UI uses object-cover, do crop in Cloudinary (prevents stretching)
        if ($cover) {
            // g_auto focuses on the subject
            $transforms[] = "c_fill";
            $transforms[] = "g_auto";
        }

        // Sharpen: good for large banners; keep moderate
        if ($sharpen) {
            // You can switch to e_unsharp_mask for more natural sharpening:
            // $transforms[] = "e_unsharp_mask:80";
            $transforms[] = "e_sharpen:80";
        }

        $transformString = implode(',', $transforms);
        return self::injectTransforms($url, $transformString);
    }

    /**
     * Generate srcset for responsive images.
     * Use cover=true for object-cover containers.
     */
    public static function srcset(
        ?string $url,
        array $widths = [640, 768, 1024, 1280, 1920, 2500],
        bool $sharpen = false,
        bool $cover = true,
        string $quality = 'good'
    ): ?string {
        if (!$url || !is_string($url)) return null;
        if (!self::isCloudinary($url)) return null;

        $parts = [];
        foreach ($widths as $w) {
            $w = (int) $w;
            $parts[] = self::optimize($url, $w, $sharpen, $cover, $quality) . " {$w}w";
        }
        return implode(', ', $parts);
    }

    // Convenience methods

    public static function swiperSlide(?string $url): ?string
    {
        // Large hero-ish slide: sharpen + higher quality
        return self::optimize($url, 1920, true, true, 'good');
    }

    public static function card(?string $url): ?string
    {
        return self::optimize($url, 1280, true, true, 'good');
    }

    public static function thumbnail(?string $url, int $size = 300): ?string
    {
        // Thumbs: no need super high quality; still crisp due to dpr_auto
        return self::optimize($url, $size, false, true, 'eco');
    }
}
