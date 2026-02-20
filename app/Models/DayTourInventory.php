<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DayTourInventory extends Model
{
    use HasFactory;

    protected $table = 'day_tour_inventory';

    protected $fillable = [
        'day_tour_id',
        'date',
        'max_groups',
        'booked_groups',
        'available_groups',
        'is_blocked',
        'block_reason'
    ];

    protected $casts = [
        'date' => 'date',
        'is_blocked' => 'boolean',
    ];

    /**
     * Boot method to auto-calculate available_groups
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($inventory) {
            // Auto-calculate available groups
            if ($inventory->is_blocked) {
                $inventory->available_groups = 0;
            } else {
                $inventory->available_groups = max(0, $inventory->max_groups - $inventory->booked_groups);
            }
        });
    }

    /**
     * Relationship to DayTour
     */
    public function dayTour()
    {
        return $this->belongsTo(DayTour::class);
    }

    /**
     * Check if inventory is available for booking
     */
    public function isAvailable(): bool
    {
        return !$this->is_blocked && $this->available_groups > 0;
    }

    /**
     * Get availability status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        if ($this->is_blocked) {
            return '<span class="badge bg-danger">Blocked</span>';
        }
        
        if ($this->available_groups === 0) {
            return '<span class="badge bg-danger">Full</span>';
        }
        
        if ($this->available_groups <= 1) {
            return '<span class="badge bg-warning">Limited</span>';
        }
        
        return '<span class="badge bg-success">Available</span>';
    }

    /**
     * Get status color for calendar
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->is_blocked || $this->available_groups === 0) {
            return 'red'; // Full/Blocked
        }
        
        if ($this->available_groups <= 1) {
            return 'yellow'; // Limited
        }
        
        return 'green'; // Available
    }

    /**
     * Scope for available inventory
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_blocked', false)
                     ->where('available_groups', '>', 0);
    }

    /**
     * Scope for specific date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Increment booked groups
     */
    public function bookGroup(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $this->increment('booked_groups');
        $this->save();
        
        return true;
    }

    /**
     * Decrement booked groups (for cancellations)
     */
    public function releaseGroup(): bool
    {
        if ($this->booked_groups > 0) {
            $this->decrement('booked_groups');
            $this->save();
            return true;
        }
        
        return false;
    }
}
