<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTour;
use App\Models\DayTourInventory;
use App\Models\PackageInventory;
use App\Services\DayTourInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DayTourInventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(DayTourInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get calendar data for a package
     */
    public function getCalendar(Request $request, int $dayTourId)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $calendarData = $this->inventoryService->getCalendarData(
            $dayTourId,
            $request->start_date,
            $request->end_date
        );

        return response()->json([
            'success' => true,
            'data' => $calendarData
        ]);
    }

    /**
     * Update inventory for a specific date
     */
    public function updateInventory(Request $request, int $dayTourId)
    {
        $request->validate([
            'date' => 'required|date',
            'max_groups' => 'required|integer|min:0|max:20',
            'is_blocked' => 'boolean',
            'block_reason' => 'nullable|string|max:255'
        ]);

        try {
            $inventory = $this->inventoryService->getOrCreateInventory($dayTourId, $request->date);

            // Update max groups
            $this->inventoryService->updateMaxGroups($dayTourId, $request->date, $request->max_groups);

            // Handle blocking/unblocking
            if ($request->has('is_blocked')) {
                if ($request->is_blocked) {
                    $this->inventoryService->blockDate($dayTourId, $request->date, $request->block_reason);
                } else {
                    $this->inventoryService->unblockDate($dayTourId, $request->date);
                }
            }

            // Get updated inventory
            $updatedInventory = $this->inventoryService->getOrCreateInventory($dayTourId, $request->date);

            return response()->json([
                'success' => true,
                'message' => 'Inventory updated successfully',
                'data' => [
                    'date' => $request->date,
                    'max_groups' => $updatedInventory->max_groups,
                    'booked_groups' => $updatedInventory->booked_groups,
                    'available_groups' => $updatedInventory->available_groups,
                    'is_blocked' => $updatedInventory->is_blocked,
                    'status' => $updatedInventory->status_color
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating inventory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Block a date
     */
    public function blockDate(Request $request, int $dayTourId)
    {
        $request->validate([
            'date' => 'required|date',
            'reason' => 'nullable|string|max:255'
        ]);

        $success = $this->inventoryService->blockDate($dayTourId, $request->date, $request->reason);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Date blocked successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to block date'
        ], 500);
    }

    /**
     * Unblock a date
     */
    public function unblockDate(Request $request, int $dayTourId)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $success = $this->inventoryService->unblockDate($dayTourId, $request->date);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Date unblocked successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to unblock date'
        ], 500);
    }

    /**
     * Get availability for all packages on a date
     */
    public function getAllAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $availability = $this->inventoryService->getAllPackagesAvailability($request->date);

        return response()->json([
            'success' => true,
            'date' => $request->date,
            'packages' => $availability
        ]);
    }

    /**
     * Bulk update inventory for a date range
     */
    public function bulkUpdate(Request $request, int $dayTourId)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_groups' => 'required|integer|min:0|max:20',
            'apply_to_weekdays' => 'nullable|array',
            'apply_to_weekdays.*' => 'in:0,1,2,3,4,5,6'
        ]);

        try {
            $currentDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $updatedCount = 0;
            $weekdays = $request->apply_to_weekdays;

            while ($currentDate <= $endDate) {
                if (empty($weekdays) || in_array($currentDate->dayOfWeek, $weekdays)) {
                    $this->inventoryService->updateMaxGroups(
                        $dayTourId,
                        $currentDate->format('Y-m-d'),
                        $request->max_groups
                    );
                    $updatedCount++;
                }

                $currentDate->addDay();
            }

            return response()->json([
                'success' => true,
                'message' => "Updated {$updatedCount} days successfully",
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error in bulk update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize inventory for a new package
     */
    public function initialize(Request $request, int $dayTourId)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'default_max_groups' => 'integer|min:1|max:20'
        ]);

        try {
            $this->inventoryService->initializeInventory(
                $dayTourId,
                $request->start_date,
                $request->end_date,
                $request->default_max_groups ?? 3
            );

            return response()->json([
                'success' => true,
                'message' => 'Inventory initialized successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error initializing inventory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick set inventory for next 30 days
     */
    public function quickset(Request $request)
    {
        $request->validate([
            'day_tour_id' => 'required|integer|exists:day_tours,id',
            'quantity' => 'required|integer|min:1|max:20'
        ]);

        try {
            $startDate = Carbon::now();
            $endDate = Carbon::now()->addDays(30);
            $currentDate = $startDate->copy();
            $updatedCount = 0;

            while ($currentDate <= $endDate) {
                $this->inventoryService->updateMaxGroups(
                    $request->day_tour_id,
                    $currentDate->format('Y-m-d'),
                    $request->quantity
                );
                $updatedCount++;
                $currentDate->addDay();
            }

            return response()->json([
                'success' => true,
                'message' => "Quick set applied successfully for {$updatedCount} days",
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error in quickset: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply quick set: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inventory for a day tour package (next 30 days)
     */
    public function getInventory($dayTourId)
    {
        // Get day tour for fallback quantity
        $dayTour = DayTour::find($dayTourId);
        $fallbackQuantity = $dayTour ? ($dayTour->max_groups_per_day ?? 3) : 0;

        // Get existing inventory records
        $inventoryRecords = DayTourInventory::where('day_tour_id', $dayTourId)
            ->whereDate('date', '>=', Carbon::now())
            ->orderBy('date')
            ->get(['date', 'max_groups', 'is_blocked'])
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
                ? (int) $inventoryRecords[$dateStr]->max_groups
                : $fallbackQuantity;

            $isAvailable = isset($inventoryRecords[$dateStr])
                ? !$inventoryRecords[$dateStr]->is_blocked
                : true;

            $items[] = [
                'date' => $dateStr,
                'min_quantity' => $quantity,
                'is_available' => $isAvailable,
            ];

            $current->addDay();
        }

        return response()->json(['items' => $items]);
    }

    /**
     * Store inventory for a date range
     */
    public function store(Request $request)
    {
        $request->validate([
            'day_tour_id' => 'required|integer|exists:day_tours,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quantity' => 'required|integer|min:1|max:20',
            'is_available' => 'boolean'
        ]);

        try {
            $currentDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $updatedCount = 0;

            while ($currentDate <= $endDate) {
                $this->inventoryService->updateMaxGroups(
                    $request->day_tour_id,
                    $currentDate->format('Y-m-d'),
                    $request->quantity
                );

                if ($request->has('is_available') && !$request->is_available) {
                    $this->inventoryService->blockDate($request->day_tour_id, $currentDate->format('Y-m-d'), 'Not available');
                } else {
                    $this->inventoryService->unblockDate($request->day_tour_id, $currentDate->format('Y-m-d'));
                }

                $updatedCount++;
                $currentDate->addDay();
            }

            return response()->json([
                'success' => true,
                'message' => "Inventory saved successfully for {$updatedCount} days",
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing inventory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to store inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new inventory record
     */
    public function storeInventory(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|integer|exists:packages,id',
            'date' => 'required|date|after_or_equal:today',
            'max_groups' => 'required|integer|min:1|max:20',
            'booked_groups' => 'nullable|integer|min:0',
            'is_blocked' => 'nullable|boolean',
            'block_reason' => 'nullable|string|max:255'
        ]);

        try {
            $packageId = $validated['package_id'];
            $date = $validated['date'];

            // Check if inventory already exists for this date
            $existing = \App\Models\PackageInventory::where('package_id', $packageId)
                ->whereDate('date', $date)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory already exists for this date',
                    'errors' => ['date' => ['Inventory already exists for this date']]
                ], 422);
            }

            // Create new inventory
            $inventory = \App\Models\PackageInventory::create([
                'package_id' => $packageId,
                'date' => $date,
                'max_groups' => $validated['max_groups'],
                'booked_groups' => $validated['booked_groups'] ?? 0,
                'is_blocked' => $validated['is_blocked'] ?? false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inventory created successfully',
                'data' => $inventory
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating inventory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update inventory record
     */
    public function updateInventoryRecord(Request $request, PackageInventory $inventory)
    {
        $validated = $request->validate([
            'max_groups' => 'required|integer|min:1|max:20',
            'booked_groups' => 'nullable|integer|min:0',
            'is_blocked' => 'nullable|boolean',
            'block_reason' => 'nullable|string|max:255'
        ]);

        try {
            $inventory->update([
                'max_groups' => $validated['max_groups'],
                'booked_groups' => $validated['booked_groups'] ?? $inventory->booked_groups,
                'is_blocked' => $validated['is_blocked'] ?? $inventory->is_blocked,
                'block_reason' => $validated['block_reason'] ?? $inventory->block_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inventory updated successfully',
                'data' => $inventory
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating inventory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete inventory record
     */
    public function destroyInventory(PackageInventory $inventory)
    {
        try {
            $inventory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Inventory deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting inventory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete inventory: ' . $e->getMessage()
            ], 500);
        }
    }
}
