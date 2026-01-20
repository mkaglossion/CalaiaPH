<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodSwiperImage extends Model
{
    use HasFactory;

    protected $fillable = ['food_id', 'title', 'image_path', 'public_id'];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }
}