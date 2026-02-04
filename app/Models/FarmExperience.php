<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmExperience extends Model
{
    protected $fillable = [
        'title',
        'desc',
        'time',
        'capacity',
        'icon',
        'cta',
        'badge',
        'img',
        'sort_order'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
