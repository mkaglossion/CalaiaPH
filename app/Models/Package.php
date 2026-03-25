<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;

    protected $table = 'packages';

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'image_public_id',
        'max_guests',
        'pricing_type',
        'is_active',
        'max_groups_per_day',
        'price_configs',
        'slug',
        'duration_days',
        'inclusions',
        'sessions',
    ];

    protected $casts = [
        'max_guests' => 'integer',
        'is_active' => 'boolean',
        'max_groups_per_day' => 'integer',
        'price_configs' => 'array',
        'inclusions' => 'array',
        'sessions' => 'array',
        'price' => 'decimal:2',
    ];

    /**
     * Package content/descriptions
     */
    public function contents()
    {
        return $this->hasMany(PackageContent::class, 'package_id');
    }

    /**
     * Package images for carousel
     */
    public function images()
    {
        return $this->hasMany(PackageImage::class, 'package_id');
    }

    /**
     * Package availability/inventory
     */
    public function inventory()
    {
        return $this->hasMany(PackageInventory::class, 'package_id');
    }

    /**
     * Activities included in package
     */
    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'package_activity', 'package_id', 'activity_id');
    }

    /**
     * Bookings for this package
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'package_id');
    }

    /**
     * Get inventory for a specific date
     */
    public function getInventoryForDate($date)
    {
        $maxGroups = $this->max_groups_per_day ?? 3;
        
        $inventory = $this->inventory()->where('date', $date)->first();
        
        if (!$inventory) {
            $inventory = $this->inventory()->create([
                'date' => $date,
                'max_groups' => $maxGroups,
                'booked_groups' => 0,
                'available_groups' => $maxGroups,
                'is_blocked' => false
            ]);
        }
        
        return $inventory;
    }

    /**
     * Check if package is available for a specific date
     */
    public function isAvailableForDate($date): bool
    {
        $inventory = $this->getInventoryForDate($date);
        return $inventory->available_groups > 0;
    }

    /**
     * Get available slots for a date
     */
    public function getAvailableSlotsForDate($date): int
    {
        $inventory = $this->getInventoryForDate($date);
        return $inventory->available_groups;
    }

    /**
     * Scope to get only active packages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get packages within price range
     */
    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    /**
     * Get display price formatted
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₱' . number_format($this->price, 2);
    }
}
