<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodPackage extends Model
{
    protected $table = 'food_packages';

    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'meal_type',
        'sort_order',
        'image',
        'is_active',
        'stock_quantity',
        'is_inventory_managed'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_inventory_managed' => 'boolean',
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the foods included in this package
     */
    public function foods()
    {
        return $this->belongsToMany(Food::class, 'food_package_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Get package items (with quantity info)
     */
    public function packageItems()
    {
        return $this->hasMany(FoodPackageItem::class);
    }

    /**
     * Calculate total price of all items in package
     */
    public function calculateItemsTotal()
    {
        return $this->foods()
            ->get()
            ->sum(function ($food) {
                return $food->price * $food->pivot->quantity;
            });
    }

    /**
     * Get effective price (package price or sum of items)
     */
    public function getEffectivePrice()
    {
        return $this->price ?? $this->calculateItemsTotal();
    }

    /**
     * Get savings if package price is set
     */
    public function getSavings()
    {
        if (!$this->price) {
            return 0;
        }
        return max(0, $this->calculateItemsTotal() - $this->price);
    }
}
