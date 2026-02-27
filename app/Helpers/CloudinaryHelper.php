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

    /**
     * Upload a base64 encoded image to Cloudinary
     *
     * @param string $base64Image Base64 encoded image data (with or without data URI prefix)
     * @param string $folder Folder path in Cloudinary (e.g., 'shop-ids', 'discount-ids')
     * @param string|null $publicId Optional public ID for the image
     * @return array Upload result with secure_url, public_id, etc.
     * @throws \Exception
     */
    public static function uploadBase64Image(string $base64Image, string $folder = 'uploads', ?string $publicId = null): array
    {
        // Remove data URI prefix if present (e.g., "data:image/jpeg;base64,")
        if (str_contains($base64Image, ',')) {
            $base64Image = explode(',', $base64Image)[1];
        }

        // Decode base64
        $imageData = base64_decode($base64Image);
        if ($imageData === false) {
            throw new \Exception('Invalid base64 image data');
        }

        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'cloudinary_');
        file_put_contents($tempFile, $imageData);

        try {
            // Get Cloudinary configuration
            $cloudName = config('cloudinary.cloud_name');
            $apiKey = config('cloudinary.api_key');
            $apiSecret = config('cloudinary.api_secret');

            if (!$cloudName || !$apiKey || !$apiSecret) {
                throw new \Exception('Cloudinary credentials not configured');
            }

            // Generate timestamp and signature
            $timestamp = time();
            $params = [
                'folder' => $folder,
                'timestamp' => $timestamp,
            ];

            if ($publicId) {
                $params['public_id'] = $publicId;
            }

            // Generate signature
            $signatureString = http_build_query($params) . $apiSecret;
            $signature = sha1($signatureString);

            // Prepare multipart form data
            $postFields = [
                'file' => new \CURLFile($tempFile),
                'api_key' => $apiKey,
                'timestamp' => $timestamp,
                'folder' => $folder,
                'signature' => $signature,
            ];

            if ($publicId) {
                $postFields['public_id'] = $publicId;
            }

            // Upload to Cloudinary
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            // Clean up temp file
            unlink($tempFile);

            if ($curlError) {
                throw new \Exception('cURL Error: ' . $curlError);
            }

            if ($httpCode !== 200) {
                throw new \Exception('Cloudinary upload failed with HTTP ' . $httpCode . ': ' . $response);
            }

            $result = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from Cloudinary');
            }

            if (isset($result['error'])) {
                throw new \Exception('Cloudinary error: ' . ($result['error']['message'] ?? 'Unknown error'));
            }

            return $result;

        } catch (\Exception $e) {
            // Clean up temp file on error
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw $e;
        }
    }
}
