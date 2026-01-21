<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Staycation;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'booking_type', // 'staycation' or 'dayvisit'
        'checkin_date',
        'checkout_date',
        'booking_data', // JSON field for all booking details
        'total_amount',
        'payment_method',
        'payment_status',
        'status', // 'confirmed', 'cancelled', 'completed'
    ];

    protected $casts = [
        'booking_data' => 'array',
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Fallback display name if customer_name is missing.
     */
    public function getDisplayCustomerNameAttribute(): string
    {
        $name = trim((string) $this->customer_name);
        if (!empty($name) && $name !== 'N/A') {
            return $name;
        }

        $data = $this->booking_data ?? [];
        $first = $data['customer_info']['first_name'] ?? $data['first_name'] ?? '';
        $last = $data['customer_info']['last_name'] ?? $data['last_name'] ?? '';
        $fallback = trim($first . ' ' . $last);

        if (empty($fallback)) {
            $fallback = $data['customer_info']['name'] ?? $data['name'] ?? '';
        }

        // Return N/A if no valid name found (don't derive from email)
        return !empty($fallback) ? $fallback : 'N/A';
    }

    /**
     * Human-friendly label for type/subtype (e.g., Standard Glamping, Dome Tent).
     */
    public function getDisplayTypeLabelAttribute(): string
    {
        $label = ucfirst($this->booking_type ?? '');
        $data = $this->booking_data ?? [];

        if ($this->booking_type === 'staycation') {
            $accommodation = $data['accommodations'][0] ?? null;
            if ($accommodation) {
                $staycationId = $accommodation['room_id'] ?? $accommodation['id'] ?? null;
                if ($staycationId) {
                    $staycation = Staycation::find($staycationId);
                    if ($staycation) {
                        return $staycation->name;
                    }
                }
                $label = $accommodation['name']
                    ?? $accommodation['package']
                    ?? $accommodation['room_type']
                    ?? $label;
            }
        } else {
            $label = $data['package']
                ?? $data['package_name']
                ?? $data['type']
                ?? $label;
        }

        return $label ?: 'N/A';
    }

    /**
     * Reduce inventory for all accommodations in this booking
     */
    public function reduceInventory()
    {
        if ($this->booking_type !== 'staycation') {
            return;
        }

        $bookingData = $this->booking_data;
        $accommodations = $bookingData['accommodations'] ?? [];
        
        $checkin = Carbon::parse($this->checkin_date);
        $checkout = Carbon::parse($this->checkout_date);
        
        // Loop through each night
        $currentDate = $checkin->copy();
        while ($currentDate->lt($checkout)) {
            // Reduce inventory for each accommodation on this date
            foreach ($accommodations as $accommodation) {
                // Try both 'room_id' and 'id' keys for compatibility
                $staycationId = $accommodation['room_id'] ?? $accommodation['id'] ?? null;
                $quantity = $accommodation['quantity'] ?? 1;
                
                if ($staycationId && $quantity > 0) {
                    try {
                        StaycationInventory::reduceForDate(
                            $staycationId,
                            $currentDate->toDateString(),
                            $quantity
                        );
                        Log::info("Reduced inventory for staycation {$staycationId} on {$currentDate->toDateString()} by {$quantity}");
                    } catch (\Exception $e) {
                        // Log error but don't fail the booking
                        Log::error("Failed to reduce inventory for booking {$this->id}: " . $e->getMessage());
                    }
                }
            }
            
            $currentDate->addDay();
        }
    }

    /**
     * Restore inventory for all accommodations in this booking (for cancellations)
     */
    public function restoreInventory()
    {
        if ($this->booking_type !== 'staycation') {
            return;
        }

        $bookingData = $this->booking_data;
        $accommodations = $bookingData['accommodations'] ?? [];
        
        $checkin = Carbon::parse($this->checkin_date);
        $checkout = Carbon::parse($this->checkout_date);
        
        // Loop through each night
        $currentDate = $checkin->copy();
        while ($currentDate->lt($checkout)) {
            // Restore inventory for each accommodation on this date
            foreach ($accommodations as $accommodation) {
                // Try both 'room_id' and 'id' keys for compatibility
                $staycationId = $accommodation['room_id'] ?? $accommodation['id'] ?? null;
                $quantity = $accommodation['quantity'] ?? 1;
                
                if ($staycationId && $quantity > 0) {
                    try {
                        StaycationInventory::increaseForDate(
                            $staycationId,
                            $currentDate->toDateString(),
                            $quantity
                        );
                        Log::info("Restored inventory for staycation {$staycationId} on {$currentDate->toDateString()} by {$quantity}");
                    } catch (\Exception $e) {
                        // Log error but don't fail the operation
                        Log::error("Failed to restore inventory for booking {$this->id}: " . $e->getMessage());
                    }
                }
            }
            
            $currentDate->addDay();
        }
    }

    /**
     * Cancel this booking and restore inventory
     */
    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
        
        $this->restoreInventory();
    }
}
