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

        $current = $startDate->clone();
        while ($current <= $endDate) {
            $qty = (isset($validated['is_available']) && $validated['is_available'] === false)
                ? 0
                : $validated['quantity'];

            StaycationInventory::updateOrCreate(
                [
                    'staycation_id' => $validated['room_id'],
                    'date' => $current->format('Y-m-d')
                ],
                [
                    'available_quantity' => $qty
                ]
            );
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

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $current = $startDate->clone();
        $count = 0;
        while ($current <= $endDate) {
            StaycationInventory::updateOrCreate(
                [
                    'staycation_id' => $validated['room_id'],
                    'date' => $current->format('Y-m-d')
                ],
                [
                    'available_quantity' => $validated['quantity']
                ]
            );
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

        $startDate = Carbon::now();
        $endDate = $startDate->clone()->addDays(30);

        $current = $startDate->clone();
        $count = 0;
        while ($current <= $endDate) {
            StaycationInventory::updateOrCreate(
                [
                    'staycation_id' => $validated['room_id'],
                    'date' => $current->format('Y-m-d')
                ],
                [
                    'available_quantity' => $validated['quantity']
                ]
            );
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

        return response()->json(['success' => true, 'message' => 'Inventory deleted']);
    }

    public function getInventory($roomId)
    {
        $items = StaycationInventory::where('staycation_id', $roomId)
            ->whereDate('date', '>=', Carbon::now())
            ->orderBy('date')
            ->limit(30)
            ->get(['date', 'available_quantity']);

        // Normalize shape for frontend compatibility
        $normalized = $items->map(function ($i) {
            return [
                'date' => $i->date->format('Y-m-d'),
                'min_quantity' => (int) $i->available_quantity,
                'is_available' => (int) $i->available_quantity > 0,
            ];
        });

        return response()->json(['items' => $normalized]);
    }
}
