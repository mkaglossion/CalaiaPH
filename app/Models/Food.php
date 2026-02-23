<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = ['name', 'category', 'subcategory', 'price', 'description', 'image', 'is_active', 'is_special_option', 'stock_quantity', 'is_inventory_managed'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_special_option' => 'boolean',
        'stock_quantity' => 'integer',
        'is_inventory_managed' => 'boolean',
    ];

    public function texts()
    {
        return $this->hasMany(FoodText::class);
    }

    public function swiperImages()
    {
        // Order by created_at ascending (1, 2, 3 - oldest first) to match admin panel
        return $this->hasMany(FoodSwiperImage::class)->orderBy('created_at', 'asc');
    }
}
