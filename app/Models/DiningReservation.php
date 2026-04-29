<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiningReservation extends Model
{
    protected $fillable = [
        'type',
        'reservation_date',
        'reservation_time',
        'guests',
        'selected_foods',
        'customer_name',
        'email',
        'phone',
        'total_amount',
        'notes',
        'status',
        'messenger_reference',
        'reservation_data',
        // Legacy fields for backward compatibility
        'food_id',
        'food_name',
        'meal_period',
        'adults',
        'first_name',
        'last_name',
        'special_requests',
        'payment_status',
        'payment_method',
        'payment_notes'
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'selected_foods' => 'array',
        'reservation_data' => 'array',
        'total_amount' => 'decimal:2'
    ];

    /**
     * Get display name for reservation type
     */
    public function getTypeLabel(): string
    {
        if ($this->type) {
            return match($this->type) {
                'day_visit' => 'Day Visit',
                'overnight_stay' => 'Overnight Stay',
                default => 'Dining'
            };
        }
        return 'Dining Reservation';
    }

    /**
     * Get status badge color
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'confirmed' => 'green',
            'completed' => 'blue',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Mark as confirmed
     */
    public function confirm(): bool
    {
        return $this->update(['status' => 'confirmed']);
    }

    /**
     * Mark as completed
     */
    public function complete(): bool
    {
        return $this->update(['status' => 'completed']);
    }

    /**
     * Mark as cancelled
     */
    public function cancel(): bool
    {
        return $this->update(['status' => 'cancelled']);
    }

    public function food()
    {
        return $this->belongsTo(Food::class);
    }
}
