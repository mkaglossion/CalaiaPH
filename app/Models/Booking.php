<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Staycation;
use App\Events\FoodStockUpdated;
use App\Models\Food;

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
        'subtotal', // Amount before VAT
        'vat_amount', // 12% VAT amount
        'vat_rate', // VAT rate applied
        'is_vat_inclusive', // Whether prices include VAT
        'payment_method',
        'payment_status',
        'status', // 'confirmed', 'cancelled', 'completed'
        'checkin_status',
        'checkin_timestamp',
        'checkout_timestamp',
    ];

    protected $casts = [
        'booking_data' => 'array',
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'checkin_timestamp' => 'datetime',
        'checkout_timestamp' => 'datetime',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'vat_rate' => 'decimal:4',
        'is_vat_inclusive' => 'boolean',
        'is_vat_exempt' => 'boolean',
    ];

    const CHECKIN_STATUS_PENDING = 'pending';
    const CHECKIN_STATUS_CHECKED_IN = 'checked_in';
    const CHECKIN_STATUS_CHECKED_OUT = 'checked_out';

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
            }
        }

        return $label;
    }

    /**
     * Calculate and set VAT fields for this booking.
     * Uses VatService for Philippine tax compliance (12% VAT).
     * 
     * @param float $totalAmount The total amount (VAT-inclusive for B2C)
     * @param bool $isVatInclusive Whether the amount includes VAT
     * @return void
     */
    public function calculateAndSetVat(float $totalAmount, bool $isVatInclusive = true): void
    {
        $vatService = app(\App\Services\VatService::class);
        $breakdown = $vatService->generateBreakdown($totalAmount, $isVatInclusive);

        $this->total_amount = $breakdown['gross_amount'];
        $this->subtotal = $breakdown['net_amount'];
        $this->vat_amount = $breakdown['vat_amount'];
        $this->vat_rate = $breakdown['vat_rate'];
        $this->is_vat_inclusive = $isVatInclusive;
    }

    /**
     * Get VAT breakdown for this booking.
     * 
     * @return array
     */
    public function getVatBreakdown(): array
    {
        return [
            'gross_amount' => (float) $this->total_amount,
            'net_amount' => (float) $this->subtotal,
            'vat_amount' => (float) $this->vat_amount,
            'vat_rate' => (float) $this->vat_rate,
            'vat_rate_display' => (float) $this->vat_rate * 100,
            'vat_label' => ((float) $this->vat_rate * 100) . '% VAT',
            'is_vat_inclusive' => (bool) $this->is_vat_inclusive,
        ];
    }

    /**
     * Get formatted VAT amount for display.
     * 
     * @return string
     */
    public function getFormattedVatAttribute(): string
    {
        return '₱' . number_format((float) $this->vat_amount, 2);
    }

    /**
     * Get formatted subtotal for display.
     * 
     * @return string
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '₱' . number_format((float) $this->subtotal, 2);
    }

    /**
     * Reduce inventory for all accommodations in this booking
     */
    public function reduceInventory(bool $strict = false)
    {
        $bookingData = $this->booking_data ?? [];
        $bookingType = $this->booking_type ?? ($bookingData['booking_type'] ?? null);
        $staycationTypes = ['staycation', 'glamping', 'camping'];

        if (!in_array($bookingType, $staycationTypes, true)) {
            return;
        }

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
                        Log::error("Failed to reduce inventory for booking {$this->id}: " . $e->getMessage());
                        if ($strict) {
                            throw $e;
                        }
                    }
                }
            }
            
            $currentDate->addDay();
        }

        // Reduce food inventory
        $this->reduceFoodInventory($strict);
    }

    /**
     * Reduce food inventory for selected foods in this booking
     */
    public function reduceFoodInventory(bool $strict = false)
    {
        $bookingData = $this->booking_data ?? [];
        $selectedFoods = $bookingData['selected_foods'] ?? [];

        foreach ($selectedFoods as $foodData) {
            $foodId = $foodData['id'] ?? null;
            $quantity = $foodData['quantity'] ?? 1;

            if ($foodId && $quantity > 0) {
                try {
                    $food = Food::find($foodId);
                    if ($food && $food->is_inventory_managed && $food->stock_quantity >= $quantity) {
                        $food->decrement('stock_quantity', $quantity);
                        broadcast(new FoodStockUpdated($food))->toOthers();
                        Log::info("Reduced food inventory for food {$foodId} by {$quantity}");
                    } else {
                        $message = "Insufficient stock or food not found for food {$foodId}";
                        Log::warning($message);
                        if ($strict) {
                            throw new \Exception($message);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to reduce food inventory for booking {$this->id}: " . $e->getMessage());
                    if ($strict) {
                        throw $e;
                    }
                }
            }
        }
    }

    /**
     * Restore inventory for all accommodations in this booking (for cancellations)
     */
    public function restoreInventory()
    {
        $bookingData = $this->booking_data ?? [];
        $bookingType = $this->booking_type ?? ($bookingData['booking_type'] ?? null);
        $staycationTypes = ['staycation', 'glamping', 'camping'];

        if (!in_array($bookingType, $staycationTypes, true)) {
            return;
        }

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

        // Restore food inventory
        $this->restoreFoodInventory();
    }

    /**
     * Restore food inventory for selected foods in this booking
     */
    public function restoreFoodInventory()
    {
        $bookingData = $this->booking_data ?? [];
        $selectedFoods = $bookingData['selected_foods'] ?? [];

        foreach ($selectedFoods as $foodData) {
            $foodId = $foodData['id'] ?? null;
            $quantity = $foodData['quantity'] ?? 1;

            if ($foodId && $quantity > 0) {
                try {
                    $food = Food::find($foodId);
                    if ($food && $food->is_inventory_managed) {
                        $food->increment('stock_quantity', $quantity);
                        broadcast(new FoodStockUpdated($food))->toOthers();
                        Log::info("Restored food inventory for food {$foodId} by {$quantity}");
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to restore food inventory for booking {$this->id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Relationship: Booking has many timelines
     */
    public function timelines()
    {
        return $this->hasMany(BookingTimeline::class);
    }

    /**
     * Log a status change in the timeline
     */
    public function logStatusChange($oldStatus, $newStatus, $triggeredBy = 'system', $adminId = null)
    {
        BookingTimeline::log(
            $this->id,
            'status_changed',
            "Status changed from " . ucfirst($oldStatus) . " to " . ucfirst($newStatus),
            $triggeredBy,
            $adminId,
            $oldStatus,
            $newStatus,
            'status'
        );
    }

    /**
     * Log a payment event
     */
    public function logPaymentEvent($eventType, $amount, $method, $metadata = null)
    {
        BookingTimeline::log(
            $this->id,
            $eventType,
            "Payment of ₱" . number_format($amount, 2) . " via " . ucfirst($method),
            'system',
            null,
            null,
            $method,
            'payment_method',
            $metadata
        );
    }

    /**
     * Log check-in event
     */
    public function logCheckIn()
    {
        BookingTimeline::log(
            $this->id,
            'checkin_completed',
            'Guest checked in',
            'system',
            null,
            null,
            self::CHECKIN_STATUS_CHECKED_IN,
            'checkin_status'
        );
    }

    /**
     * Log check-out event
     */
    public function logCheckOut()
    {
        BookingTimeline::log(
            $this->id,
            'checkout_completed',
            'Guest checked out',
            'system',
            null,
            null,
            self::CHECKIN_STATUS_CHECKED_OUT,
            'checkin_status'
        );
    }

    /**
     * Cancel this booking and restore inventory
     */
    public function cancel()
    {
        $oldStatus = $this->status;
        $this->status = 'cancelled';
        $this->save();
        
        // Log the cancellation
        $this->logStatusChange($oldStatus, 'cancelled', 'system');
        
        $this->restoreInventory();
    }
}
