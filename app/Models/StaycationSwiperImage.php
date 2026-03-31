<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\CloudinaryHelper;
use Illuminate\Support\Facades\Log;

class StaycationSwiperImage extends Model
{
    protected $fillable = ['staycation_id', 'title', 'image_path', 'public_id'];

    public function staycation()
    {
        return $this->belongsTo(Staycation::class);
    }

    /**
     * Get the properly formatted image URL for displaying
     * Handles different storage formats (full URLs, public_ids, filenames)
     */
    public function getImageUrlAttribute(): ?string
    {
        // If image_path is already a full Cloudinary URL, return it optimized
        if ($this->image_path && str_contains($this->image_path, 'res.cloudinary.com')) {
            $result = CloudinaryHelper::swiperSlide($this->image_path);
            Log::debug('StaycationSwiperImage.image_url - Full URL case', [
                'image_path' => $this->image_path,
                'result' => $result
            ]);
            return $result;
        }

        // Try public_id first
        $publicId = $this->public_id ?? $this->image_path;
        
        if ($publicId) {
            // If it doesn't look like a full URL, treat as public_id or filename
            if (!str_contains($publicId, '/') && !str_contains($publicId, 'http')) {
                $cloudName = config('cloudinary.cloud_name');
                $url = "https://res.cloudinary.com/{$cloudName}/image/upload/{$publicId}";
                $result = CloudinaryHelper::swiperSlide($url);
                Log::debug('StaycationSwiperImage.image_url - Constructed URL case', [
                    'publicId' => $publicId,
                    'cloudName' => $cloudName,
                    'constructed_url' => $url,
                    'result' => $result
                ]);
                return $result;
            }
        }

        // Return image_path as-is if it exists (might be local or external URL)
        Log::debug('StaycationSwiperImage.image_url - Fallback case', [
            'image_path' => $this->image_path,
            'public_id' => $this->public_id
        ]);
        return $this->image_path ?? null;
    }
}

