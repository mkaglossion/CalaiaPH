<?php

namespace App\Http\Controllers\Admin;

use App\Models\Activity;
use App\Models\ActivityPackage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Cloudinary\Cloudinary;

class ActivityPackageController extends Controller
{
    /**
     * Display a listing of activity packages.
     */
    public function apiIndex()
    {
        $packages = ActivityPackage::with(['activities' => function($q) {
            $q->select('activities.id', 'activities.name', 'activities.price')
              ->orderBy('activities.name');
        }])->withCount('activities')->latest()->get();

        $packagesData = $packages->map(function($package) {
            return [
                'id' => $package->id,
                'name' => $package->name,
                'description' => $package->description,
                'category' => $package->category,
                'image' => $package->image,
                'effective_price' => $package->getEffectivePrice(),
                'is_active' => $package->is_active,
                'activities_count' => $package->activities_count,
                'activities' => $package->activities->map(function($activity) {
                    return [
                        'id' => $activity->id,
                        'name' => $activity->name,
                        'price' => (float) $activity->price
                    ];
                })->values(),
                'edit_url' => route('admin.activity-packages.edit', $package),
                'delete_url' => route('admin.activity-packages.destroy', $package),
            ];
        });

        return response()->json(['packages' => $packagesData]);
    }

    /**
     * Store a newly created activity package.
     */
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|unique:activity_packages',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_inventory_managed' => 'boolean',
            'is_active' => 'boolean',
            'activities' => 'nullable|array',
            'activities.*' => 'integer|exists:activities,id',
            'quantities' => 'nullable|array',
            'quantities.*' => 'integer|min:1',
            'image' => 'nullable|image|max:10240',
        ]);

        // Upload image if provided
        $imagePath = null;
        if ($request->hasFile('image')) {
            $result = app(Cloudinary::class)->uploadApi()->upload(
                $request->file('image')->getRealPath(),
                ['folder' => 'calaiaph/activity-packages']
            );
            $imagePath = $result['secure_url'];
        }

        $package = ActivityPackage::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'price' => $validated['price'],
            'image' => $imagePath,
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'is_inventory_managed' => $validated['is_inventory_managed'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Attach activities with quantities
        if (!empty($validated['activities'])) {
            $activitiesToAttach = [];
            foreach ($validated['activities'] as $index => $activity_id) {
                $quantity = $validated['quantities'][$index] ?? 1;
                $activitiesToAttach[$activity_id] = ['quantity' => $quantity];
            }
            $package->activities()->attach($activitiesToAttach);
        }

        return response()->json([
            'success' => true,
            'message' => 'Activity package created successfully',
            'package' => $package
        ]);
    }

    /**
     * Display the specified activity package.
     */
    public function show(ActivityPackage $activityPackage)
    {
        return response()->json([
            'id' => $activityPackage->id,
            'name' => $activityPackage->name,
            'description' => $activityPackage->description,
            'category' => $activityPackage->category,
            'price' => $activityPackage->price,
            'image' => $activityPackage->image,
            'is_active' => $activityPackage->is_active,
            'stock_quantity' => $activityPackage->stock_quantity,
            'is_inventory_managed' => $activityPackage->is_inventory_managed,
            'activities' => $activityPackage->activities->map(function ($activity) {
                return [
                    'activity_id' => $activity->id,
                    'name' => $activity->name,
                    'price' => $activity->price,
                    'quantity' => $activity->pivot->quantity
                ];
            })
        ]);
    }

    /**
     * Show the form for editing the specified activity package.
     */
    public function edit(ActivityPackage $activityPackage)
    {
        return response()->json([
            'id' => $activityPackage->id,
            'name' => $activityPackage->name,
            'description' => $activityPackage->description,
            'category' => $activityPackage->category,
            'price' => $activityPackage->price,
            'image' => $activityPackage->image,
            'stock_quantity' => $activityPackage->stock_quantity,
            'is_inventory_managed' => $activityPackage->is_inventory_managed,
            'is_active' => $activityPackage->is_active,
            'activities' => $activityPackage->activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'price' => $activity->price,
                    'pivot' => [
                        'quantity' => $activity->pivot->quantity ?? 1
                    ]
                ];
            })
        ]);
    }

    /**
     * Update the specified activity package.
     */
    public function update(Request $request, ActivityPackage $activityPackage)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:activity_packages,name,' . $activityPackage->id,
            'description' => 'nullable|string',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_inventory_managed' => 'boolean',
            'is_active' => 'boolean',
            'activities' => 'nullable|array',
            'activities.*' => 'integer|exists:activities,id',
            'quantities' => 'nullable|array',
            'quantities.*' => 'integer|min:1',
            'image' => 'nullable|image|max:10240',
        ]);

        $activityPackage->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'is_inventory_managed' => $validated['is_inventory_managed'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Handle image
        if ($request->hasFile('image')) {
            $result = app(Cloudinary::class)->uploadApi()->upload(
                $request->file('image')->getRealPath(),
                ['folder' => 'calaiaph/activity-packages']
            );
            $activityPackage->update(['image' => $result['secure_url']]);
        }

        // Update activities with quantities
        if (!empty($validated['activities'])) {
            $activitiesToSync = [];
            foreach ($validated['activities'] as $index => $activity_id) {
                $quantity = $validated['quantities'][$index] ?? 1;
                $activitiesToSync[$activity_id] = ['quantity' => $quantity];
            }
            $activityPackage->activities()->sync($activitiesToSync);
        } else {
            $activityPackage->activities()->detach();
        }

        return response()->json([
            'success' => true,
            'message' => 'Activity package updated successfully',
            'package' => $activityPackage
        ]);
    }

    /**
     * Remove the specified activity package.
     */
    public function destroy(ActivityPackage $activityPackage)
    {
        $activityPackage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activity package deleted successfully'
        ]);
    }
}
