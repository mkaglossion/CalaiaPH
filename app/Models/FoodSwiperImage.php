<?php

namespace App\Models;

use App\Models\Concerns\HasSortableOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodSwiperImage extends Model
{
    use HasFactory, HasSortableOrder;

    protected $fillable = ['food_id', 'title', 'image_path', 'public_id', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }
}
