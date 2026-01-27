<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'description',
        'price',
        'image',
        'category',
        'is_active',
        'stock_quantity',
        'is_inventory_managed',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'is_inventory_managed' => 'boolean',
        'is_active' => 'boolean',
    ];
}
