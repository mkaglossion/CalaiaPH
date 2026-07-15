<?php

namespace App\Models;

use App\Models\Concerns\HasSortableOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageSwiperImage extends Model
{
    use HasFactory;
    use HasSortableOrder;

    protected $table = 'package_swiper_images';

    protected $fillable = [
        'image_path',
        'title',
        'public_id',
        'sort_order',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
