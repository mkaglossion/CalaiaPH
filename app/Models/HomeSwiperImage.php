<?php

namespace App\Models;

use App\Models\Concerns\HasSortableOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSwiperImage extends Model
{
    use HasFactory, HasSortableOrder;

    protected $table = 'home_swiper_images';

    protected $fillable = ['title', 'image_path', 'public_id', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
