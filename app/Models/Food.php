<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = ['name', 'category', 'price', 'description', 'image', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function texts()
    {
        return $this->hasMany(FoodText::class);
    }

    public function swiperImages()
    {
        return $this->hasMany(FoodSwiperImage::class);
    }
}
