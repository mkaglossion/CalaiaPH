<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TestimonialSectionSetting extends Model
{
    protected $fillable = [
        'background_image_path',
        'background_image_public_id',
    ];

    public static function current(): ?self
    {
        if (!Schema::hasTable('testimonial_section_settings')) {
            return null;
        }

        return static::query()->first();
    }
}
