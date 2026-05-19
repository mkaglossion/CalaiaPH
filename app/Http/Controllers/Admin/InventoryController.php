<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staycation;
use App\Models\StaycationInventory;
use App\Models\InventoryAuditLog;
use App\Models\Food;
use App\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function bulkUpsert(Request $request)
    {
        $validated = $request->validate([
            'staycation_id' => 'required|exists:staycations,id',
            'date_from' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d|after_or_equal:date_from',
            'total_quantity' => 'required|integer|min:1',
            'is_blocked' => 'nullable|boolean',
            'block_reason' => 'nullable|string|max:255',
        ]);

        $dateFrom = Carbon::parse($validated['date_from'])->startOfDay();
        $dateTo = Carbon::parse($validated['date_to'])->startOfDay();
        $days = $dateFrom->diffInDays($dateTo) + 1;

        if ($days > 366) {
            return response()->json([
                'success' => false,
                'message' => 'Date range is too large. Please limit to 366 days.',
            ], 422);
        }

        $isBlocked = (bool) ($validated['is_blocked'] ?? false);
        $totalQty = (int) $validated['total_quantity'];

        $existing = StaycationInventory::where('staycation_id', $validated['staycation_id'])
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->date)->toDateString());

        $results = [];

        DB::transaction(function () use ($validated, $dateFrom, $dateTo, $existing, $isBlocked, $totalQty, &$results) {
            $period = CarbonPeriod::create($dateFrom, $dateTo);
            foreach ($period as $date) {
                $dateKey = $date->toDateString();
                $current = $existing->get($dateKey);
                $bookedQty = (int) ($current->booked_quantity ?? 0);
                $availableQty = $isBlocked ? 0 : max(0, $totalQty - $bookedQty);

                $inventory = StaycationInventory::updateOrCreate(
                    [
                        'staycation_id' => $validated['staycation_id'],
                        'date' => $dateKey,
                    ],
                    [
                        'total_quantity' => $totalQty,
                        'booked_quantity' => $bookedQty,
                        'available_quantity' => $availableQty,
                        'is_blocked' => $isBlocked,
                        'block_reason' => $validated['block_reason'] ?? null,
                    ]
                );

                $results[] = $inventory;
            }
        });

        $this->logInventoryBulkAction($request, 'staycation', 'bulk_upsert', (int) $validated['staycation_id'], count($results), [
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'total_quantity' => $totalQty,
            'is_blocked' => $isBlocked,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Inventory upserted for {$days} day(s).",
            'data' => $results,
        ]);
    }

    public function bulkDeleteByIds(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:staycation_inventories,id',
        ]);

        $deleted = StaycationInventory::whereIn('id', $validated['ids'])->delete();

        $this->logInventoryBulkAction($request, 'staycation', 'bulk_delete', null, $deleted, [
            'ids_count' => count($validated['ids']),
            'ids' => $validated['ids'],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} inventory record(s).",
            'deleted' => $deleted,
        ]);
    }

    public function store(Request $request)
    {
        // Handle both old API format and new inventory management form format
        if ($request->has('staycation_id')) {
            // New inventory management form format
            $validated = $request->validate([
                'staycation_id' => 'required|exists:staycations,id',
                'date' => 'required|date_format:Y-m-d',
                'total_quantity' => 'required|integer|min:1',
                'is_blocked' => 'boolean',
                'block_reason' => 'nullable|string'
            ]);

            // Check if inventory already exists to preserve booked_quantity
            $existing = StaycationInventory::where('staycation_id', $validated['staycation_id'])
                ->where('date', $validated['date'])
                ->first();

            $bookedQty = $existing ? $existing->booked_quantity : 0;
            $totalQty = $validated['total_quantity'];
            $availableQty = max(0, $totalQty - $bookedQty);

            // If blocked, set available to 0
            if ($validated['is_blocked'] ?? false) {
                $availableQty = 0;
            }

            $inventory = StaycationInventory::updateOrCreate(
                [
                    'staycation_id' => $validated['staycation_id'],
                    'date' => $validated['date']
                ],
                [
                    'total_quantity' => $totalQty,
                    'booked_quantity' => $bookedQty,
                    'available_quantity' => $availableQty,
                    'is_blocked' => $validated['is_blocked'] ?? false,
                    'block_reason' => $validated['block_reason'] ?? null
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Inventory saved successfully',
                'data' => $inventory
            ], 201);
        }

        // Old API format (for backward compatibility)
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
        $baseQuantity = $staycation ? $staycation->base_quantity : 0;

        // If setting higher than base, update staycation quantity
        $newBase = max($baseQuantity, $validated['quantity']);
        if ($newBase > $baseQuantity) {
            $staycation->update(['base_quantity' => $newBase]);
            $baseQuantity = $newBase;
        }

        $current = $startDate->clone();
        while ($current < $endDate) {
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
        $baseQuantity = $staycation ? $staycation->base_quantity : 0;

        // Update base if higher
        $newBase = max($baseQuantity, $validated['quantity']);
        if ($newBase > $baseQuantity) {
            $staycation->update(['base_quantity' => $newBase]);
            $baseQuantity = $newBase;
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $current = $startDate->clone();
        $count = 0;
        while ($current < $endDate) {
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
        $baseQuantity = $staycation ? $staycation->base_quantity : 0;

        // Update base if higher
        $newBase = max($baseQuantity, $validated['quantity']);
        if ($newBase > $baseQuantity) {
            $staycation->update(['base_quantity' => $newBase]);
            $baseQuantity = $newBase;
        }

        $startDate = Carbon::now();
        $endDate = $startDate->clone()->addDays(30);

        $current = $startDate->clone();
        $count = 0;
        while ($current < $endDate) {
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

    public function update(Request $request, StaycationInventory $inventory)
    {
        try {
            if (!$inventory) {
                return response()->json(['success' => false, 'message' => 'Inventory not found'], 404);
            }

            $validated = $request->validate([
                'staycation_id' => 'required|exists:staycations,id',
                'date' => 'required|date_format:Y-m-d',
                'total_quantity' => 'required|integer|min:1',
                'is_blocked' => 'boolean',
                'block_reason' => 'nullable|string'
            ]);

            // Calculate available_quantity based on total and booked
            $bookedQty = $inventory->booked_quantity ?? 0;
            $totalQty = $validated['total_quantity'];
            $availableQty = max(0, $totalQty - $bookedQty);

            // If blocked, set available to 0
            if ($validated['is_blocked'] ?? false) {
                $availableQty = 0;
            }

            $inventory->update([
                'total_quantity' => $totalQty,
                'available_quantity' => $availableQty,
                'is_blocked' => $validated['is_blocked'] ?? false,
                'block_reason' => $validated['block_reason'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inventory updated successfully',
                'data' => $inventory
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request, ?StaycationInventory $inventory = null)
    {
        // Handle inventory management form API (delete by ID/model)
        if ($inventory) {
            $inventory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Inventory deleted successfully'
            ]);
        }

        // Handle old API format (delete by room_id and date)
        $validated = $request->validate([
            'room_id' => 'required|exists:staycations,id',
            'date' => 'required|date'
        ]);

        StaycationInventory::where('staycation_id', $validated['room_id'])
            ->where('date', $validated['date'])
            ->delete();

        // Get fallback quantity for broadcasting
        $staycation = Staycation::find($validated['room_id']);
        $fallbackQuantity = $staycation ? ($staycation->base_quantity ?? 0) : 0;

        // Broadcast inventory update (back to fallback quantity)
        broadcast(new \App\Events\InventoryUpdated($validated['room_id'], $validated['date'], $fallbackQuantity));

        return response()->json(['success' => true, 'message' => 'Inventory deleted']);
    }

    public function getInventory($roomId)
    {
        // Get staycation for fallback quantity
        $staycation = Staycation::find($roomId);
        $fallbackQuantity = $staycation ? ($staycation->base_quantity ?? 0) : 0;

        // Get existing inventory records starting from today, ordered by date
        $inventoryRecords = StaycationInventory::where('staycation_id', $roomId)
            ->whereDate('date', '>=', Carbon::today())
            ->orderBy('date')
            ->get(['date', 'available_quantity']);

        // Convert to array with date string as key
        $recordsByDate = $inventoryRecords->keyBy(function ($item) {
            return $item->date->format('Y-m-d');
        });

        // Build items ONLY from configured inventory dates (not continuous)
        $items = [];
        foreach ($inventoryRecords as $record) {
            $dateStr = $record->date->format('Y-m-d');
            $quantity = (int) $record->available_quantity;

            $items[] = [
                'date' => $dateStr,
                'min_quantity' => $quantity,
                'is_available' => $quantity > 0,
            ];
        }

        // If no inventory configured, show up to 30 days with fallback quantity for reference
        if (empty($items)) {
            $current = Carbon::today();
            for ($i = 0; $i < 30; $i++) {
                $dateStr = $current->format('Y-m-d');
                $items[] = [
                    'date' => $dateStr,
                    'min_quantity' => $fallbackQuantity,
                    'is_available' => $fallbackQuantity > 0,
                ];
                $current->addDay();
            }
        }

        return response()->json(['items' => $items]);
    }

    /**
     * Display food inventory monitoring page
     */
    public function food(Request $request)
    {
        $query = Food::where('is_inventory_managed', true);

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'out_of_stock':
                    $query->where('stock_quantity', '<=', 0);
                    break;
                case 'low_stock':
                    $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5);
                    break;
                case 'in_stock':
                    $query->where('stock_quantity', '>', 5);
                    break;
            }
        }

        $foods = $query->orderBy('name')->paginate(10);

        // Get available categories for filter dropdown
        $categories = Food::where('is_inventory_managed', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        return view('admin.inventory.food', compact('foods', 'categories'));
    }

    /**
     * Display shop inventory monitoring page
     */
    public function shop(Request $request)
    {
        $query = Product::where('is_inventory_managed', true);

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'out_of_stock':
                    $query->where('stock_quantity', '<=', 0);
                    break;
                case 'low_stock':
                    $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5);
                    break;
                case 'in_stock':
                    $query->where('stock_quantity', '>', 5);
                    break;
            }
        }

        $products = $query->orderBy('title')->paginate(10);

        // Get available categories for filter dropdown
        $categories = Product::where('is_inventory_managed', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        return view('admin.inventory.shop', compact('products', 'categories'));
    }

    private function logInventoryBulkAction(
        Request $request,
        string $entityType,
        string $action,
        ?int $entityId,
        int $affectedCount,
        array $payload = []
    ): void {
        try {
            InventoryAuditLog::create([
                'user_id' => auth()->id(),
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'action' => $action,
                'affected_count' => $affectedCount,
                'payload' => $payload,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to write inventory audit log', [
                'entity_type' => $entityType,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
