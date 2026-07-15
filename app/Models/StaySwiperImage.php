<?php

namespace App\Models;

use App\Models\Concerns\HasSortableOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaySwiperImage extends Model
{
    use HasFactory, HasSortableOrder;

    protected $fillable = ['title', 'image_path', 'public_id', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
