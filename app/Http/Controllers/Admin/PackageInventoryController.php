<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageInventory;
use App\Models\InventoryAuditLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PackageInventoryController extends Controller
{
    private ?bool $supportsBlockReason = null;

    public function bulkUpsert(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|integer|exists:packages,id',
            'date_from' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d|after_or_equal:date_from',
            'max_groups' => 'required|integer|min:1|max:20',
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
        $maxGroups = (int) $validated['max_groups'];
        $supportsBlockReason = $this->packageInventorySupportsBlockReason();

        $existing = PackageInventory::where('package_id', $validated['package_id'])
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->date)->toDateString());

        $results = [];

        DB::transaction(function () use ($validated, $dateFrom, $dateTo, $existing, $isBlocked, $maxGroups, $supportsBlockReason, &$results) {
            $period = CarbonPeriod::create($dateFrom, $dateTo);
            foreach ($period as $date) {
                $dateKey = $date->toDateString();
                $current = $existing->get($dateKey);
                $bookedGroups = (int) ($current->booked_groups ?? 0);
                $availableGroups = $isBlocked ? 0 : max(0, $maxGroups - $bookedGroups);

                $updatePayload = [
                    'max_groups' => $maxGroups,
                    'booked_groups' => $bookedGroups,
                    'available_groups' => $availableGroups,
                    'is_blocked' => $isBlocked,
                ];
                if ($supportsBlockReason) {
                    $updatePayload['block_reason'] = $validated['block_reason'] ?? null;
                }

                $inventory = PackageInventory::updateOrCreate(
                    [
                        'package_id' => $validated['package_id'],
                        'date' => $dateKey,
                    ],
                    $updatePayload
                );

                $results[] = $inventory;
            }
        });

        $this->logInventoryBulkAction($request, 'package', 'bulk_upsert', (int) $validated['package_id'], count($results), [
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'max_groups' => $maxGroups,
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
            'ids.*' => 'integer|exists:package_inventory,id',
        ]);

        $deleted = PackageInventory::whereIn('id', $validated['ids'])->delete();

        $this->logInventoryBulkAction($request, 'package', 'bulk_delete', null, $deleted, [
            'ids_count' => count($validated['ids']),
            'ids' => $validated['ids'],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} inventory record(s).",
            'deleted' => $deleted,
        ]);
    }

    /**
     * Display package inventory management page
     */
    public function index()
    {
        $packages = Package::with(['inventory' => function ($query) {
            $query->where('date', '>=', Carbon::today())->orderBy('date');
        }])->get();
        return view('admin.packages.inventory', compact('packages'));
    }

    /**
     * Store new inventory record for a package
     */
    public function storeInventory(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|integer|exists:packages,id',
            'date' => 'required|date|after_or_equal:today',
            'max_groups' => 'required|integer|min:1|max:20',
            'is_blocked' => 'nullable|boolean',
            'block_reason' => 'nullable|string|max:255'
        ]);

        try {
            $packageId = $validated['package_id'];
            $date = $validated['date'];
            $supportsBlockReason = $this->packageInventorySupportsBlockReason();

            // Check if inventory already exists for this date
            $existing = PackageInventory::where('package_id', $packageId)
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
            $createPayload = [
                'package_id' => $packageId,
                'date' => $date,
                'max_groups' => $validated['max_groups'],
                'is_blocked' => $validated['is_blocked'] ?? false
            ];
            if ($supportsBlockReason) {
                $createPayload['block_reason'] = $validated['block_reason'] ?? null;
            }
            $inventory = PackageInventory::create($createPayload);

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
            'is_blocked' => 'nullable|boolean',
            'block_reason' => 'nullable|string|max:255'
        ]);

        try {
            $supportsBlockReason = $this->packageInventorySupportsBlockReason();
            $updatePayload = [
                'max_groups' => $validated['max_groups'],
                'is_blocked' => $validated['is_blocked'] ?? $inventory->is_blocked,
            ];
            if ($supportsBlockReason) {
                $updatePayload['block_reason'] = $validated['block_reason'] ?? $inventory->block_reason;
            }
            $inventory->update($updatePayload);

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

    /**
     * Get inventory for a package
     */
    public function getInventory($packageId)
    {
        try {
            $package = Package::find($packageId);
            
            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            $inventory = PackageInventory::where('package_id', $packageId)
                ->orderBy('date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $inventory
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching inventory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch inventory: ' . $e->getMessage()
            ], 500);
        }
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
            Log::warning('Failed to write inventory audit log', [
                'entity_type' => $entityType,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function packageInventorySupportsBlockReason(): bool
    {
        if ($this->supportsBlockReason === null) {
            $this->supportsBlockReason = Schema::hasColumn('package_inventory', 'block_reason');
        }

        return $this->supportsBlockReason;
    }
}
