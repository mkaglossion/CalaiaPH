<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class Staycation extends Model
{
    protected $fillable = ['name', 'days', 'type', 'quantity', 'capacity', 'adults', 'children', 'image', 'price_configs', 'price'];

    protected $casts = [
        'price_configs' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($staycation) {
            // If quantity changed, sync inventory
            if ($staycation->wasChanged('quantity')) {
                // Run sync for this staycation
                Artisan::call('staycation:sync-inventory', [
                    '--days' => 90,
                    '--staycation' => $staycation->id
                ]);
            }
        });
    }

    public function texts()
    {
        return $this->hasMany(StaycationText::class);
    }

    public function swiperImages()
    {
        return $this->hasMany(StaycationSwiperImage::class);
    }

    public function inventories()
    {
        return $this->hasMany(StaycationInventory::class);
    }

    // Accommodations via variants
    public function staycationAccommodations()
    {
        return $this->belongsToMany(
            StaycationAccommodation::class,
            'accommodation_variants',
            'staycation_id',
            'staycation_accommodation_id'
        );
    }

    // Accessor for today's available quantity
    public function getTodayAvailableAttribute()
    {
        $today = now()->toDateString();
        $inventory = StaycationInventory::where('staycation_id', $this->id)
            ->where('date', $today)
            ->first();
        return $inventory ? $inventory->available_quantity : 0;
    }

}

