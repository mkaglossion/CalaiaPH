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
        'discount_type', // 'senior', 'pwd', or null
        'discount_amount', // Discount amount
        'discount_rate', // Discount rate applied
        'is_vat_exempt', // Whether the booking is VAT-exempt (for senior/PWD)
        'payment_method',
        'payment_status',
        'status', // 'confirmed', 'cancelled', 'completed'
        'checkin_status',
        'checkin_timestamp',
        'checkout_timestamp',
        // Discount verification fields
        'discount_id_number',
        'discount_id_image',
        'discount_verification_status',
        'discount_rejection_reason',
        'discount_verified_at',
        'discount_verified_by',
        'discount_claimed_at_checkin',
        'discount_difference_paid',
        'discount_difference_paid_at',
        'discount_verification_method',
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
        'discount_amount' => 'decimal:2',
        'discount_rate' => 'decimal:4',
        'is_vat_exempt' => 'boolean',
        'discount_verified_at' => 'datetime',
        'discount_difference_paid_at' => 'datetime',
        'discount_claimed_at_checkin' => 'boolean',
        'discount_difference_paid' => 'decimal:2',
    ];

    const CHECKIN_STATUS_PENDING = 'pending';
    const CHECKIN_STATUS_CHECKED_IN = 'checked_in';
    const CHECKIN_STATUS_CHECKED_OUT = 'checked_out';

    // Discount verification statuses
    const DISCOUNT_STATUS_NOT_REQUIRED = 'not_required';
    const DISCOUNT_STATUS_PENDING = 'pending';
    const DISCOUNT_STATUS_VERIFIED = 'verified';
    const DISCOUNT_STATUS_REJECTED = 'rejected';

    // Verification methods
    const VERIFICATION_METHOD_NONE = 'none';
    const VERIFICATION_METHOD_UPLOAD = 'upload';
    const VERIFICATION_METHOD_PRESENT_AT_CHECKIN = 'present_at_checkin';

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
     * Calculate and set discount fields for this booking.
     * 
     * @param float $originalAmount The original amount before discount
     * @param string $discountType The type of discount (senior, pwd, or none)
     * @return void
     */
    public function calculateAndSetDiscount(float $originalAmount, string $discountType): void
    {
        $discountService = app(\App\Services\DiscountService::class);
        $breakdown = $discountService->applyDiscount($originalAmount, $discountType);

        $this->discount_type = $breakdown['discount_type'];
        $this->discount_rate = $breakdown['discount_rate'] / 100; // Store as decimal
        $this->discount_amount = $breakdown['discount_amount'];
        $this->is_vat_exempt = $breakdown['is_vat_exempt'];
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
        // If VAT-exempt (senior/PWD), no VAT calculation
        if ($this->is_vat_exempt) {
            $this->vat_amount = 0;
            $this->vat_rate = 0;
            $this->subtotal = $totalAmount;
            $this->total_amount = $totalAmount;
            $this->is_vat_inclusive = true;
            return;
        }

        $vatService = app(\App\Services\VatService::class);
        $breakdown = $vatService->generateBreakdown($totalAmount, $isVatInclusive);

        $this->total_amount = $breakdown['gross_amount'];
        $this->subtotal = $breakdown['net_amount'];
        $this->vat_amount = $breakdown['vat_amount'];
        $this->vat_rate = $breakdown['vat_rate'];
        $this->is_vat_inclusive = $isVatInclusive;
    }

    /**
     * Get discount breakdown for this booking.
     * 
     * @return array
     */
    public function getDiscountBreakdown(): array
    {
        $discountService = app(\App\Services\DiscountService::class);
        
        return [
            'discount_type' => $this->discount_type,
            'discount_rate' => (float) $this->discount_rate,
            'discount_rate_display' => (float) $this->discount_rate * 100,
            'discount_amount' => (float) $this->discount_amount,
            'discount_label' => $discountService->getDiscountLabel($this->discount_type ?? 'none'),
            'is_vat_exempt' => (bool) $this->is_vat_exempt,
        ];
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
     * Get formatted discount amount for display.
     * 
     * @return string
     */
    public function getFormattedDiscountAttribute(): string
    {
        return '₱' . number_format((float) $this->discount_amount, 2);
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
    public function reduceInventory()
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
                        // Log error but don't fail the booking
                        Log::error("Failed to reduce inventory for booking {$this->id}: " . $e->getMessage());
                    }
                }
            }
            
            $currentDate->addDay();
        }

        // Reduce food inventory
        $this->reduceFoodInventory();
    }

    /**
     * Reduce food inventory for selected foods in this booking
     */
    public function reduceFoodInventory()
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
                        Log::warning("Insufficient stock or food not found for food {$foodId}");
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to reduce food inventory for booking {$this->id}: " . $e->getMessage());
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
     * Cancel this booking and restore inventory
     */
    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
        
        $this->restoreInventory();
    }
}
