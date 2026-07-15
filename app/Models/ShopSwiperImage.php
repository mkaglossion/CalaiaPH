<?php

namespace App\Models;

use App\Models\Concerns\HasSortableOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopSwiperImage extends Model
{
    use HasFactory, HasSortableOrder;

    protected $table = 'shop_swiper_images';

    protected $fillable = [
        'title',
        'image_path',
        'public_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
