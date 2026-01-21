<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StaycationInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'staycation_id',
        'date',
        'total_quantity',
        'booked_quantity',
        'available_quantity',
    ];

    protected $casts = [
        'date' => 'date',
        'total_quantity' => 'integer',
        'booked_quantity' => 'integer',
        'available_quantity' => 'integer',
    ];

    public function staycation()
    {
        return $this->belongsTo(Staycation::class);
    }

    /**
     * Reduce inventory for a specific date
     */
    public static function reduceForDate($staycationId, $date, $quantity)
    {
        return DB::transaction(function () use ($staycationId, $date, $quantity) {
            $inventory = self::lockForUpdate()
                ->where('staycation_id', $staycationId)
                ->where('date', $date)
                ->first();

            if (!$inventory) {
                throw new \Exception("Inventory not found for staycation {$staycationId} on {$date}");
            }

            if ($inventory->available_quantity < $quantity) {
                throw new \Exception("Insufficient inventory for staycation {$staycationId} on {$date}. Available: {$inventory->available_quantity}, Requested: {$quantity}");
            }

            $inventory->booked_quantity += $quantity;
            $inventory->available_quantity -= $quantity;
            $inventory->save();

            // Broadcast inventory update event
            broadcast(new \App\Events\InventoryUpdated($staycationId, $date, $inventory->available_quantity));

            return $inventory;
        });
    }

    /**
     * Increase inventory for a specific date (for cancellations)
     */
    public static function increaseForDate($staycationId, $date, $quantity)
    {
        return DB::transaction(function () use ($staycationId, $date, $quantity) {
            $inventory = self::lockForUpdate()
                ->where('staycation_id', $staycationId)
                ->where('date', $date)
                ->first();

            if (!$inventory) {
                throw new \Exception("Inventory not found for staycation {$staycationId} on {$date}");
            }

            $inventory->booked_quantity = max(0, $inventory->booked_quantity - $quantity);
            $inventory->available_quantity = min($inventory->total_quantity, $inventory->available_quantity + $quantity);
            $inventory->save();

            // Broadcast inventory update event
            broadcast(new \App\Events\InventoryUpdated($staycationId, $date, $inventory->available_quantity));

            return $inventory;
        });
    }

    /**
     * Get available quantity for a specific date
     */
    public static function getAvailableForDate($staycationId, $date)
    {
        $inventory = self::where('staycation_id', $staycationId)
            ->where('date', $date)
            ->first();

        // If inventory exists for this date, return it
        if ($inventory) {
            return $inventory->available_quantity;
        }

        // If no inventory set for this date, fallback to staycation's base quantity
        $staycation = \App\Models\Staycation::find($staycationId);
        return $staycation ? ($staycation->quantity ?? 0) : 0;
    }
}
