<?php

namespace App\Services;

class InventoryService
{
    /**
     * Get default inventory configuration
     */
    public function getDefaultInventory(): array
    {
        $today = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');
        $dayAfter = now()->addDays(2)->format('Y-m-d');

        return [
            'day-visit' => [$today => 8, $tomorrow => 5, $dayAfter => 10],
            'room' => [$today => 3, $tomorrow => 2, $dayAfter => 5],
            'special-event' => [$today => 2, $tomorrow => 1, $dayAfter => 3],
        ];
    }

    /**
     * Reduce inventory for a specific type and date
     */
    public function reduceInventory(string $type, string $date): void
    {
        $inventory = session('inventory', $this->getDefaultInventory());

        if (isset($inventory[$type][$date])) {
            $inventory[$type][$date] = max(0, $inventory[$type][$date] - 1);
            session(['inventory' => $inventory]);
        }
    }

    /**
     * Check if inventory is available for a specific type and date
     */
    public function isAvailable(string $type, string $date): bool
    {
        $inventory = session('inventory', $this->getDefaultInventory());
        return isset($inventory[$type][$date]) && $inventory[$type][$date] > 0;
    }

    /**
     * Get current inventory for a type and date
     */
    public function getInventory(string $type, string $date): int
    {
        $inventory = session('inventory', $this->getDefaultInventory());
        return $inventory[$type][$date] ?? 0;
    }

    /**
     * Get all current inventory
     */
    public function getAllInventory(): array
    {
        return session('inventory', $this->getDefaultInventory());
    }
}