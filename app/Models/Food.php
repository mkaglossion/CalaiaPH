<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = ['name', 'category', 'subcategory', 'price', 'description', 'image', 'is_active', 'is_special_option', 'stock_quantity', 'is_inventory_managed', 'available_for_day_visit', 'day_visit_only'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_special_option' => 'boolean',
        'stock_quantity' => 'integer',
        'is_inventory_managed' => 'boolean',
        'available_for_day_visit' => 'boolean',
        'day_visit_only' => 'boolean',
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

    public function packages()
    {
        return $this->belongsToMany(FoodPackage::class, 'food_package_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
