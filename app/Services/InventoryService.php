<?php

namespace App\Services;

class InventoryService
{
    public function isAvailable($type, $date)
    {
        $inventory = $this->getAllInventory();
        return isset($inventory[$type][$date]) && $inventory[$type][$date] > 0;
    }

    public function reduceInventory($type, $date)
    {
        $inventory = session('inventory', $this->getDefaultInventory());
        if (isset($inventory[$type][$date])) {
            $inventory[$type][$date] = max(0, $inventory[$type][$date] - 1);
            session(['inventory' => $inventory]);
        }
    }

    public function getAllInventory()
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
}