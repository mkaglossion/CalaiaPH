<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopSwiperImage extends Model
{
    use HasFactory;

    protected $table = 'shop_swiper_images';

    protected $fillable = [
        'title',
        'image_path',
        'public_id',
    ];
}
