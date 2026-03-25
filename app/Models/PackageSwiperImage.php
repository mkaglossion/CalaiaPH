<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageSwiperImage extends Model
{
    use HasFactory;

    protected $table = 'package_swiper_images';

    protected $fillable = [
        'image_path',
        'title',
        'public_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
