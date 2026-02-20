<?php

namespace App\Services;

use App\Models\DayTour;
use App\Models\DayTourInventory;
use App\Events\DayTourInventoryUpdated;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DayTourInventoryService
{
    /**
     * Get or create inventory for a package on a specific date
     */
    public function getOrCreateInventory(int $dayTourId, string $date): DayTourInventory
    {
        $inventory = DayTourInventory::where('day_tour_id', $dayTourId)
            ->where('date', $date)
            ->first();

        if (!$inventory) {
            $dayTour = DayTour::findOrFail($dayTourId);
            $inventory = DayTourInventory::create([
                'day_tour_id' => $dayTourId,
                'date' => $date,
                'max_groups' => $dayTour->max_groups_per_day ?? 3,
                'booked_groups' => 0,
                'available_groups' => $dayTour->max_groups_per_day ?? 3,
                'is_blocked' => false
            ]);
        }

        return $inventory;
    }

    /**
     * Check availability for a package on a date
     */
    public function checkAvailability(int $dayTourId, string $date): array
    {
        $inventory = $this->getOrCreateInventory($dayTourId, $date);
        $dayTour = DayTour::find($dayTourId);

        return [
            'available' => $inventory->isAvailable(),
            'total_slots' => $inventory->max_groups,
            'booked_slots' => $inventory->booked_groups,
            'remaining_slots' => $inventory->available_groups,
            'is_blocked' => $inventory->is_blocked,
            'block_reason' => $inventory->block_reason,
            'package_name' => $dayTour?->name,
            'max_guests_per_group' => $dayTour?->max_guests
        ];
    }

    /**
     * Book a slot for a package
     */
    public function bookSlot(int $dayTourId, string $date, string $bookingReference = null): bool
    {
        try {
            $inventory = $this->getOrCreateInventory($dayTourId, $date);

            if (!$inventory->isAvailable()) {
                Log::warning('Attempted to book unavailable slot', [
                    'day_tour_id' => $dayTourId,
                    'date' => $date,
                    'available' => $inventory->available_groups
                ]);
                return false;
            }

            $success = $inventory->bookGroup();

            if ($success) {
                // Broadcast inventory update via Pusher
                $this->broadcastInventoryUpdate($dayTourId, $date);
                
                Log::info('Slot booked successfully', [
                    'day_tour_id' => $dayTourId,
                    'date' => $date,
                    'booking_reference' => $bookingReference,
                    'remaining' => $inventory->available_groups
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Error booking slot: ' . $e->getMessage(), [
                'day_tour_id' => $dayTourId,
                'date' => $date
            ]);
            return false;
        }
    }

    /**
     * Release a slot (for cancellations)
     */
    public function releaseSlot(int $dayTourId, string $date): bool
    {
        try {
            $inventory = $this->getOrCreateInventory($dayTourId, $date);
            
            $success = $inventory->releaseGroup();

            if ($success) {
                // Broadcast inventory update via Pusher
                $this->broadcastInventoryUpdate($dayTourId, $date);
                
                Log::info('Slot released successfully', [
                    'day_tour_id' => $dayTourId,
                    'date' => $date,
                    'remaining' => $inventory->available_groups
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Error releasing slot: ' . $e->getMessage(), [
                'day_tour_id' => $dayTourId,
                'date' => $date
            ]);
            return false;
        }
    }

    /**
     * Block a date for a package
     */
    public function blockDate(int $dayTourId, string $date, string $reason = null): bool
    {
        try {
            $inventory = $this->getOrCreateInventory($dayTourId, $date);
            
            $inventory->update([
                'is_blocked' => true,
                'block_reason' => $reason
            ]);

            // Broadcast update
            $this->broadcastInventoryUpdate($dayTourId, $date);

            Log::info('Date blocked', [
                'day_tour_id' => $dayTourId,
                'date' => $date,
                'reason' => $reason
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error blocking date: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Unblock a date for a package
     */
    public function unblockDate(int $dayTourId, string $date): bool
    {
        try {
            $inventory = $this->getOrCreateInventory($dayTourId, $date);
            
            $inventory->update([
                'is_blocked' => false,
                'block_reason' => null
            ]);

            // Broadcast update
            $this->broadcastInventoryUpdate($dayTourId, $date);

            Log::info('Date unblocked', [
                'day_tour_id' => $dayTourId,
                'date' => $date
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error unblocking date: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update max groups for a specific date
     */
    public function updateMaxGroups(int $dayTourId, string $date, int $maxGroups): bool
    {
        try {
            $inventory = $this->getOrCreateInventory($dayTourId, $date);
            
            $inventory->update([
                'max_groups' => $maxGroups
            ]);

            // Recalculate available groups
            $inventory->available_groups = max(0, $maxGroups - $inventory->booked_groups);
            $inventory->save();

            // Broadcast update
            $this->broadcastInventoryUpdate($dayTourId, $date);

            return true;
        } catch (\Exception $e) {
            Log::error('Error updating max groups: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get calendar data for a date range
     */
    public function getCalendarData(int $dayTourId, string $startDate, string $endDate): array
    {
        $inventories = DayTourInventory::where('day_tour_id', $dayTourId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy('date');

        $calendarData = [];
        $currentDate = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($currentDate <= $end) {
            $dateString = $currentDate->format('Y-m-d');
            $inventory = $inventories->get($dateString);

            if (!$inventory) {
                // Create default inventory entry
                $dayTour = DayTour::find($dayTourId);
                $inventory = new DayTourInventory([
                    'date' => $dateString,
                    'max_groups' => $dayTour?->max_groups_per_day ?? 3,
                    'booked_groups' => 0,
                    'available_groups' => $dayTour?->max_groups_per_day ?? 3,
                    'is_blocked' => false
                ]);
            }

            $calendarData[] = [
                'date' => $dateString,
                'day' => $currentDate->format('j'),
                'month' => $currentDate->format('M'),
                'weekday' => $currentDate->format('D'),
                'total_slots' => $inventory->max_groups,
                'booked_slots' => $inventory->booked_groups,
                'available_slots' => $inventory->available_groups,
                'is_blocked' => $inventory->is_blocked,
                'block_reason' => $inventory->block_reason,
                'status' => $inventory->status_color, // green, yellow, red
                'is_available' => $inventory->isAvailable()
            ];

            $currentDate->addDay();
        }

        return $calendarData;
    }

    /**
     * Get availability for all packages on a specific date
     */
    public function getAllPackagesAvailability(string $date): array
    {
        $packages = DayTour::where('is_active', true)->get();
        $availability = [];

        foreach ($packages as $package) {
            $inventory = $this->getOrCreateInventory($package->id, $date);
            
            $availability[] = [
                'package_id' => $package->id,
                'package_name' => $package->name,
                'max_guests' => $package->max_guests,
                'total_slots' => $inventory->max_groups,
                'available_slots' => $inventory->available_groups,
                'is_available' => $inventory->isAvailable(),
                'status' => $inventory->status_color
            ];
        }

        return $availability;
    }

    /**
     * Broadcast inventory update via Pusher
     */
    private function broadcastInventoryUpdate(int $dayTourId, string $date): void
    {
        try {
            $availability = $this->checkAvailability($dayTourId, $date);
            
            // Dispatch the event for broadcasting to others via Pusher
            event(new DayTourInventoryUpdated([
                'day_tour_id' => $dayTourId,
                'date' => $date,
                'availability' => $availability,
                'timestamp' => now()->toIso8601String()
            ]));

            Log::debug('Inventory update broadcasted', [
                'day_tour_id' => $dayTourId,
                'date' => $date
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to broadcast inventory update: ' . $e->getMessage());
        }
    }

    /**
     * Initialize inventory for a date range (for new packages)
     */
    public function initializeInventory(int $dayTourId, string $startDate, string $endDate, int $defaultMaxGroups = 3): void
    {
        $currentDate = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($currentDate <= $end) {
            $this->getOrCreateInventory($dayTourId, $currentDate->format('Y-m-d'));
            $currentDate->addDay();
        }

        Log::info('Inventory initialized', [
            'day_tour_id' => $dayTourId,
            'start' => $startDate,
            'end' => $endDate
        ]);
    }
}
