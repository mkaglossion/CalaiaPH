<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class Staycation extends Model
{
    protected $fillable = ['name', 'days', 'type', 'base_quantity', 'capacity', 'max_guest_capacity', 'included_guest_capacity', 'adults', 'children', 'image', 'price_configs', 'price', 'sort_order'];

    protected $casts = [
        'price_configs' => 'array',
        'max_guest_capacity' => 'integer',
        'included_guest_capacity' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($staycation) {
            if (!Schema::hasColumn($staycation->getTable(), 'sort_order') || !empty($staycation->sort_order)) {
                return;
            }

            $staycation->sort_order = ((int) static::max('sort_order')) + 1;
        });

        static::updated(function ($staycation) {
            // If quantity changed, sync inventory
            if ($staycation->wasChanged('base_quantity')) {
                // Run sync for this staycation
                Artisan::call('staycation:sync-inventory', [
                    '--days' => 90,
                    '--staycation' => $staycation->id
                ]);
            }
        });
    }

    public function scopeOrdered($query)
    {
        if (Schema::hasColumn($this->getTable(), 'sort_order')) {
            return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        }

        return $query->orderBy('id', 'asc');
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
