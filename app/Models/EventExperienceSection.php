<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventExperienceSection extends Model
{
    protected $fillable = [
        'section_id',
        'title',
        'image_path',
        'description1',
        'description2',
        'link_path',
        'theme',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
