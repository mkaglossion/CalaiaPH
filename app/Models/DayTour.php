<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayTour extends Model
{
    protected $fillable = ['name', 'description', 'price', 'image', 'max_guests', 'pricing_type', 'is_active', 'max_groups_per_day', 'price_configs'];

    protected $casts = [
        'max_guests' => 'integer',
        'is_active' => 'boolean',
        'max_groups_per_day' => 'integer',
        'price_configs' => 'array',
    ];

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'day_tour_activity');
    }

    // Additional swiper images for modal (excludes package image)
    public function swiperImages()
    {
        return $this->hasMany(DayTourSwiperImage::class, 'daytour_id')->where('image_path', '!=', $this->image);
    }

    /**
     * Inventory relationship
     */
    public function inventory()
    {
        return $this->hasMany(DayTourInventory::class);
    }

    /**
     * Get inventory for a specific date
     */
    public function getInventoryForDate($date)
    {
        return $this->inventory()->firstOrCreate(
            ['date' => $date],
            [
                'max_groups' => 3, // Default capacity
                'booked_groups' => 0,
                'available_groups' => 3,
                'is_blocked' => false
            ]
        );
    }

    /**
     * Check if package is available for a specific date
     */
    public function isAvailableForDate($date): bool
    {
        $inventory = $this->getInventoryForDate($date);
        return $inventory->isAvailable();
    }

    /**
     * Get available slots for a date
     */
    public function getAvailableSlotsForDate($date): int
    {
        $inventory = $this->getInventoryForDate($date);
        return $inventory->available_groups;
    }
}
