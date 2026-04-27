<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActivityPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'price',
        'image',
        'is_active',
        'stock_quantity',
        'is_inventory_managed',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_inventory_managed' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Get the activities included in this package.
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'activity_package_items')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Calculate the effective price of the package.
     * Returns the package price if set, otherwise sums activity prices.
     */
    public function getEffectivePrice()
    {
        if ($this->price) {
            return $this->price;
        }

        return $this->activities->sum(function ($activity) {
            return ($activity->price ?? 0) * ($activity->pivot->quantity ?? 1);
        });
    }

    /**
     * Calculate the total value of all activities in the package.
     * Sums activity prices multiplied by their quantities.
     */
    public function calculateActivitiesTotal()
    {
        return $this->activities->sum(function ($activity) {
            return ($activity->price ?? 0) * ($activity->pivot->quantity ?? 1);
        });
    }
}
