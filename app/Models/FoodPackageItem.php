<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodPackageItem extends Model
{
    protected $table = 'food_package_items';

    protected $fillable = ['food_package_id', 'food_id', 'quantity'];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the package this item belongs to
     */
    public function package()
    {
        return $this->belongsTo(FoodPackage::class, 'food_package_id');
    }

    /**
     * Get the food item
     */
    public function food()
    {
        return $this->belongsTo(Food::class);
    }
}
