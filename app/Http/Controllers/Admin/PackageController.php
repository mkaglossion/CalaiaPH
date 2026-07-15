<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class PackageController extends Controller
{
    /**
     * Display a listing of packages.
     */
    public function index()
    {
        $packages = Package::ordered()->paginate(15);
        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        return redirect()->route('admin.do.whattodo');
    }

    /**
     * Store a newly created package.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:packages',
            'slug' => 'nullable|string|max:255|unique:packages',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'pricing_type' => 'required|in:per_pax,per_group',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'duration_value' => 'required|integer|min:1',
            'duration_unit' => 'required|in:minutes,hours,days',
            'max_guests' => 'required|integer|min:1',
            'max_groups_per_day' => 'required|integer|min:1',
            'inclusions' => 'nullable|string',
            'sessions' => 'nullable|string',
            'price_configs' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            // Keep legacy duration_days in sync for backwards compatibility.
            $validated['duration_days'] = $this->toDurationDays(
                (int) $validated['duration_value'],
                (string) $validated['duration_unit']
            );

            // Parse JSON fields
            if (isset($validated['inclusions'])) {
                $validated['inclusions'] = json_decode($validated['inclusions'], true) ?? [];
            }
            if (isset($validated['sessions'])) {
                $validated['sessions'] = json_decode($validated['sessions'], true) ?? [];
            }
            if (isset($validated['price_configs'])) {
                $validated['price_configs'] = json_decode($validated['price_configs'], true);
            }

            // Handle image upload to Cloudinary
            $imageData = [];
            if ($request->hasFile('image')) {
                try {
                    $file = $request->file('image');
                    $filePath = $file->getRealPath();
                    $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
                    
                    $imageData['image'] = $uploadResult['secure_url'];
                    $imageData['image_public_id'] = $uploadResult['public_id'];
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['image' => ['Failed to upload image: ' . $e->getMessage()]]
                    ], 422);
                }
            }

            // Remove image from validated if it wasn't a URL
            unset($validated['image']);

            $package = Package::create(array_merge($validated, $imageData));

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Package created successfully.',
                    'package' => $package
                ]);
            }

            return redirect()->route('admin.packages.index')
                ->with('success', 'Package created successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['general' => [$e->getMessage()]]
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Failed to create package: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing a package.
     */
    public function edit(Request $request, Package $package)
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($package);
        }

        return redirect()->route('admin.do.whattodo');
    }

    /**
     * Update the package.
     */
    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:packages,name,' . $package->id,
            'slug' => 'nullable|string|max:255|unique:packages,slug,' . $package->id,
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'pricing_type' => 'required|in:per_pax,per_group',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'duration_value' => 'required|integer|min:1',
            'duration_unit' => 'required|in:minutes,hours,days',
            'max_guests' => 'required|integer|min:1',
            'max_groups_per_day' => 'required|integer|min:1',
            'inclusions' => 'nullable|string',
            'sessions' => 'nullable|string',
            'price_configs' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            // Keep legacy duration_days in sync for backwards compatibility.
            $validated['duration_days'] = $this->toDurationDays(
                (int) $validated['duration_value'],
                (string) $validated['duration_unit']
            );

            // Parse JSON fields
            if (isset($validated['inclusions'])) {
                $validated['inclusions'] = json_decode($validated['inclusions'], true) ?? [];
            }
            if (isset($validated['sessions'])) {
                $validated['sessions'] = json_decode($validated['sessions'], true) ?? [];
            }
            if (isset($validated['price_configs'])) {
                $validated['price_configs'] = json_decode($validated['price_configs'], true);
            }

            // Handle image upload to Cloudinary
            $imageData = [];
            if ($request->hasFile('image')) {
                try {
                    // Delete old image from Cloudinary if it exists
                    if ($package->image_public_id ?? false) {
                        Cloudinary::uploadApi()->destroy($package->image_public_id);
                    }

                    // Upload new image to Cloudinary
                    $file = $request->file('image');
                    $filePath = $file->getRealPath();
                    $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
                    
                    $imageData['image'] = $uploadResult['secure_url'];
                    $imageData['image_public_id'] = $uploadResult['public_id'];
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['image' => ['Failed to upload image: ' . $e->getMessage()]]
                    ], 422);
                }
            }

            // Remove image from validated if it wasn't a file
            unset($validated['image']);

            $package->update(array_merge($validated, $imageData));

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Package updated successfully.',
                    'package' => $package
                ]);
            }

            return redirect()->route('admin.packages.index')
                ->with('success', 'Package updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['general' => [$e->getMessage()]]
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Failed to update package: ' . $e->getMessage());
        }
    }

    /**
     * Delete a package.
     */
    public function destroy(Request $request, Package $package)
    {
        try {
            // Delete image from Cloudinary if it exists
            if ($package->image_public_id ?? false) {
                Cloudinary::uploadApi()->destroy($package->image_public_id);
            }

            $package->delete();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Package deleted successfully.'
                ]);
            }

            return redirect()->route('admin.packages.index')
                ->with('success', 'Package deleted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['general' => [$e->getMessage()]]
                ], 422);
            }

            return back()->with('error', 'Failed to delete package: ' . $e->getMessage());
        }
    }

    /**
     * Toggle package active status (AJAX).
     */
    public function toggleStatus(Request $request, Package $package)
    {
        try {
            $package->update(['is_active' => !$package->is_active]);
            return response()->json([
                'success' => true,
                'message' => 'Package status updated.',
                'is_active' => $package->is_active,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
            'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ordered_ids' => ['required', 'array', 'min:1'],
            'ordered_ids.*' => ['integer', 'distinct', 'exists:packages,id'],
        ]);

        $orderedIds = array_values(array_unique(array_map('intval', $validated['ordered_ids'])));
        $currentIds = Package::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $missingIds = array_values(array_diff($currentIds, $orderedIds));
        $finalIds = array_merge($orderedIds, $missingIds);

        DB::transaction(function () use ($finalIds): void {
            foreach ($finalIds as $index => $packageId) {
                Package::whereKey($packageId)->update([
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Package order updated successfully.',
        ]);
    }

    private function toDurationDays(int $value, string $unit): int
    {
        return match ($unit) {
            'minutes' => max(1, (int) ceil($value / 1440)),
            'hours' => max(1, (int) ceil($value / 24)),
            default => max(1, $value),
        };
    }
}
