<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staycation;
use App\Models\StaycationInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:staycations,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quantity' => 'required|integer|min:0',
            'is_available' => 'boolean'
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $staycation = Staycation::find($validated['room_id']);
        $baseQuantity = $staycation ? $staycation->quantity : 0;

        // If setting higher than base, update staycation quantity
        $newBase = max($baseQuantity, $validated['quantity']);
        if ($newBase > $baseQuantity) {
            $staycation->update(['quantity' => $newBase]);
            $baseQuantity = $newBase;
        }

        $current = $startDate->clone();
        while ($current <= $endDate) {
            $qty = (isset($validated['is_available']) && $validated['is_available'] === false)
                ? 0
                : $validated['quantity']; // Allow setting any quantity

            // Get existing inventory to preserve booked_quantity if exists
            $existing = StaycationInventory::where('staycation_id', $validated['room_id'])
                ->where('date', $current->format('Y-m-d'))
                ->first();

            $totalQty = $baseQuantity; // Now base is updated
            $bookedQty = $existing ? $existing->booked_quantity : 0;

            // Ensure available doesn't go negative
            $availableQty = max(0, $qty);

            StaycationInventory::updateOrCreate(
                [
                    'staycation_id' => $validated['room_id'],
                    'date' => $current->format('Y-m-d')
                ],
                [
                    'total_quantity' => $totalQty,
                    'booked_quantity' => $bookedQty,
                    'available_quantity' => $availableQty
                ]
            );

            // Broadcast inventory update
            broadcast(new \App\Events\InventoryUpdated($validated['room_id'], $current->format('Y-m-d'), $qty));

            $current->addDay();
        }

        return response()->json(['success' => true, 'message' => 'Inventory updated']);
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:staycations,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quantity' => 'required|integer|min:1'
        ]);

        $staycation = Staycation::find($validated['room_id']);
        $baseQuantity = $staycation ? $staycation->quantity : 0;

        // Update base if higher
        $newBase = max($baseQuantity, $validated['quantity']);
        if ($newBase > $baseQuantity) {
            $staycation->update(['quantity' => $newBase]);
            $baseQuantity = $newBase;
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $current = $startDate->clone();
        $count = 0;
        while ($current <= $endDate) {
            $qty = $validated['quantity'];

            // Get existing
            $existing = StaycationInventory::where('staycation_id', $validated['room_id'])
                ->where('date', $current->format('Y-m-d'))
                ->first();

            $totalQty = $baseQuantity;
            $bookedQty = $existing ? $existing->booked_quantity : 0;
            $availableQty = max(0, $qty);

            StaycationInventory::updateOrCreate(
                [
                    'staycation_id' => $validated['room_id'],
                    'date' => $current->format('Y-m-d')
                ],
                [
                    'total_quantity' => $totalQty,
                    'booked_quantity' => $bookedQty,
                    'available_quantity' => $availableQty
                ]
            );

            // Broadcast inventory update
            broadcast(new \App\Events\InventoryUpdated($validated['room_id'], $current->format('Y-m-d'), $validated['quantity']));

            $current->addDay();
            $count++;
        }

        return response()->json(['success' => true, 'message' => "Inventory created for $count days"]);
    }

    public function quickset(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:staycations,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $staycation = Staycation::find($validated['room_id']);
        $baseQuantity = $staycation ? $staycation->quantity : 0;

        // Update base if higher
        $newBase = max($baseQuantity, $validated['quantity']);
        if ($newBase > $baseQuantity) {
            $staycation->update(['quantity' => $newBase]);
            $baseQuantity = $newBase;
        }

        $startDate = Carbon::now();
        $endDate = $startDate->clone()->addDays(30);

        $current = $startDate->clone();
        $count = 0;
        while ($current <= $endDate) {
            $qty = $validated['quantity'];

            // Get existing
            $existing = StaycationInventory::where('staycation_id', $validated['room_id'])
                ->where('date', $current->format('Y-m-d'))
                ->first();

            $totalQty = $baseQuantity;
            $bookedQty = $existing ? $existing->booked_quantity : 0;
            $availableQty = max(0, $qty);

            StaycationInventory::updateOrCreate(
                [
                    'staycation_id' => $validated['room_id'],
                    'date' => $current->format('Y-m-d')
                ],
                [
                    'total_quantity' => $totalQty,
                    'booked_quantity' => $bookedQty,
                    'available_quantity' => $availableQty
                ]
            );

            // Broadcast inventory update
            broadcast(new \App\Events\InventoryUpdated($validated['room_id'], $current->format('Y-m-d'), $validated['quantity']));

            $current->addDay();
            $count++;
        }

        return response()->json(['success' => true, 'message' => "Inventory set for $count days"]);
    }

    public function delete(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:staycations,id',
            'date' => 'required|date'
        ]);

        StaycationInventory::where('staycation_id', $validated['room_id'])
            ->where('date', $validated['date'])
            ->delete();

        // Get fallback quantity for broadcasting
        $staycation = Staycation::find($validated['room_id']);
        $fallbackQuantity = $staycation ? ($staycation->quantity ?? 0) : 0;

        // Broadcast inventory update (back to fallback quantity)
        broadcast(new \App\Events\InventoryUpdated($validated['room_id'], $validated['date'], $fallbackQuantity));

        return response()->json(['success' => true, 'message' => 'Inventory deleted']);
    }

    public function getInventory($roomId)
    {
        // Get staycation for fallback quantity
        $staycation = Staycation::find($roomId);
        $fallbackQuantity = $staycation ? ($staycation->quantity ?? 0) : 0;

        // Get existing inventory records
        $inventoryRecords = StaycationInventory::where('staycation_id', $roomId)
            ->whereDate('date', '>=', Carbon::now())
            ->orderBy('date')
            ->get(['date', 'available_quantity'])
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        // Generate next 30 days
        $items = [];
        $current = Carbon::now();
        for ($i = 0; $i < 30; $i++) {
            $dateStr = $current->format('Y-m-d');

            // Use inventory record if exists, otherwise fallback to base quantity
            $quantity = isset($inventoryRecords[$dateStr])
                ? (int) $inventoryRecords[$dateStr]->available_quantity
                : $fallbackQuantity;

            $items[] = [
                'date' => $dateStr,
                'min_quantity' => $quantity,
                'is_available' => $quantity > 0,
            ];

            $current->addDay();
        }

        return response()->json(['items' => $items]);
    }
}
