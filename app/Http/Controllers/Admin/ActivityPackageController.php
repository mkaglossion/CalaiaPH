<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageHelper;
use App\Models\Activity;
use App\Models\ActivityPackage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CloudinaryImageService;

class ActivityPackageController extends Controller
{
    public function __construct(private readonly CloudinaryImageService $cloudinaryImages)
    {
    }

    private function uploadImage(?\Illuminate\Http\UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        return $this->cloudinaryImages->upload($file, 'activity-packages')['url'];
    }

    private function packageToArray(ActivityPackage $activityPackage, bool $includeActivities = true): array
    {
        if ($includeActivities) {
            $activityPackage->loadMissing(['activities' => function ($q) {
                $q->select('activities.id', 'activities.name', 'activities.price')
                  ->orderBy('activities.name');
            }]);
        }

        $payload = [
            'id' => $activityPackage->id,
            'name' => $activityPackage->name,
            'description' => $activityPackage->description,
            'category' => $activityPackage->category,
            'price' => $activityPackage->price,
            'image' => $activityPackage->image,
            'image_url' => filled($activityPackage->image) ? ImageHelper::src($activityPackage->image) : null,
            'effective_price' => $activityPackage->getEffectivePrice(),
            'is_active' => $activityPackage->is_active,
            'stock_quantity' => $activityPackage->stock_quantity,
            'is_inventory_managed' => $activityPackage->is_inventory_managed,
        ];

        if ($includeActivities) {
            $payload['activities_count'] = $activityPackage->activities_count ?? $activityPackage->activities()->count();
            $payload['activities'] = $activityPackage->activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'price' => (float) $activity->price,
                    'pivot' => [
                        'quantity' => $activity->pivot->quantity ?? 1,
                    ],
                ];
            })->values();
        }

        return $payload;
    }

    /**
     * Display a listing of activity packages.
     */
    public function apiIndex()
    {
        $packages = ActivityPackage::with(['activities' => function($q) {
            $q->select('activities.id', 'activities.name', 'activities.price')
              ->orderBy('activities.name');
        }])->withCount('activities')->latest()->get();

        $packagesData = $packages->map(function ($package) {
            return array_merge($this->packageToArray($package), [
                'edit_url' => route('admin.activity-packages.edit', $package),
                'delete_url' => route('admin.activity-packages.destroy', $package),
            ]);
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'remove_image' => 'nullable|in:1',
        ]);

        $imagePath = $this->uploadImage($request->file('image'));

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
        return response()->json($this->packageToArray($activityPackage));
    }

    /**
     * Show the form for editing the specified activity package.
     */
    public function edit(ActivityPackage $activityPackage)
    {
        return response()->json($this->packageToArray($activityPackage));
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'remove_image' => 'nullable|in:1',
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

        if ($request->boolean('remove_image')) {
            $activityPackage->update(['image' => null]);
        } elseif ($request->hasFile('image')) {
            $activityPackage->update(['image' => $this->uploadImage($request->file('image'))]);
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
