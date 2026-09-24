<?php

namespace App\Services;

class InventoryService
{
    public function isAvailable(string $type, string $date): bool
    {
        $inventory = $this->getAllInventory();
        return isset($inventory[$type][$date]) && $inventory[$type][$date] > 0;
    }

    public function reduceInventory(string $type, string $date): void
    {
        $inventory = session('inventory', $this->getDefaultInventory());
        if (isset($inventory[$type][$date])) {
            $inventory[$type][$date] = max(0, $inventory[$type][$date] - 1);
            session(['inventory' => $inventory]);
        }
    }

    public function getAllInventory(): array
    {
        return session('inventory', $this->getDefaultInventory());
    }

    private function getDefaultInventory()
    {
        $today = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');
        $dayAfter = now()->addDays(2)->format('Y-m-d');

        return [
            'day-visit' => [$today => 8, $tomorrow => 5, $dayAfter => 10],
            'room' => [$today => 3, $tomorrow => 2, $dayAfter => 5],
        ];
    }

    /**
     * Check if staycation inventory is available for date range
     */
    public function checkStaycationAvailability(int $roomId, string $checkinDate, string $checkoutDate, int $quantity): bool|array
    {
        if (\Carbon\Carbon::parse($checkinDate)->isPast()) {
            throw new \Exception("Cannot book for past dates");
        }

        $checkin = \Carbon\Carbon::parse($checkinDate);
        $checkout = \Carbon\Carbon::parse($checkoutDate);

        while ($checkin->lt($checkout)) {
            $inventory = \App\Models\StaycationInventory::where('staycation_id', $roomId)
                ->whereDate('date', $checkin->toDateString())
                ->first();

            if (!$inventory || $inventory->available_quantity < $quantity) {
                return false;
            }

            $checkin->addDay();
        }

        return true;
    }

    /**
     * Reserve staycation inventory
     */
    public function reserveStaycationInventory(int $roomId, string $checkinDate, string $checkoutDate, int $quantity, int $bookingId): void
    {
        $checkin = \Carbon\Carbon::parse($checkinDate);
        $checkout = \Carbon\Carbon::parse($checkoutDate);

        while ($checkin->lt($checkout)) {
            $inventory = \App\Models\StaycationInventory::where('staycation_id', $roomId)
                ->whereDate('date', $checkin->toDateString())
                ->first();

            if ($inventory) {
                $inventory->update([
                    'available_quantity' => max(0, $inventory->available_quantity - $quantity),
                    'booked_quantity' => $inventory->booked_quantity + $quantity,
                ]);
            }

            $checkin->addDay();
        }
    }

    /**
     * Release staycation inventory (on cancellation)
     */
    public function releaseStaycationInventory(int $roomId, string $checkinDate, string $checkoutDate, int $quantity, int $bookingId): void
    {
        $checkin = \Carbon\Carbon::parse($checkinDate);
        $checkout = \Carbon\Carbon::parse($checkoutDate);

        while ($checkin->lt($checkout)) {
            $inventory = \App\Models\StaycationInventory::where('staycation_id', $roomId)
                ->whereDate('date', $checkin->toDateString())
                ->first();

            if ($inventory) {
                $inventory->update([
                    'available_quantity' => $inventory->available_quantity + $quantity,
                    'booked_quantity' => max(0, $inventory->booked_quantity - $quantity),
                ]);
            }

            $checkin->addDay();
        }
    }

    /**
     * Check day tour availability
     */
    public function checkDayTourAvailability(int $dayTourId, string $date, int $quantity): bool|array
    {
        $inventory = \App\Models\DayTourInventory::where('day_tour_id', $dayTourId)
            ->whereDate('date', $date)
            ->first();

        if (!$inventory) {
            return true; // Default: available if no inventory record
        }

        return $inventory->available_groups >= $quantity;
    }

    /**
     * Get minimum available quantity in a date range
     */
    public function getMinAvailableInDateRange(int $roomId, string $checkinDate, string $checkoutDate): int
    {
        $checkin = \Carbon\Carbon::parse($checkinDate);
        $checkout = \Carbon\Carbon::parse($checkoutDate);

        $minAvailable = PHP_INT_MAX;

        while ($checkin->lt($checkout)) {
            $inventory = \App\Models\StaycationInventory::where('staycation_id', $roomId)
                ->whereDate('date', $checkin->toDateString())
                ->first();

            $available = $inventory ? $inventory->available_quantity : 0;
            $minAvailable = min($minAvailable, $available);

            $checkin->addDay();
        }

        return $minAvailable === PHP_INT_MAX ? 0 : $minAvailable;
    }
}