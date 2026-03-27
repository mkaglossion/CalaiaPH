<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PackageInventoryController extends Controller
{
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
            $inventory = PackageInventory::create([
                'package_id' => $packageId,
                'date' => $date,
                'max_groups' => $validated['max_groups'],
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
            'is_blocked' => 'nullable|boolean',
            'block_reason' => 'nullable|string|max:255'
        ]);

        try {
            $inventory->update([
                'max_groups' => $validated['max_groups'],
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
}
