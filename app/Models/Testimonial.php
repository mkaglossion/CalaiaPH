<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'name',
        'position',
        'content',
        'avatar',
        'image_path',
        'image_public_id',
        'order',
        'user_id',
        'status',
        'rating'
    ];

    protected $casts = [
        'order' => 'integer',
        'rating' => 'integer'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
