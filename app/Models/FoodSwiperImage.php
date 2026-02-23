<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodSwiperImage extends Model
{
    use HasFactory;

    protected $fillable = ['food_id', 'title', 'image_path', 'public_id', 'sort_order'];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'asc');
    }
}
