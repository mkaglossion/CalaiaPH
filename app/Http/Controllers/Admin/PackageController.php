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
        $packages = Package::orderBy('created_at', 'asc')->paginate(15);
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
            'duration_days' => 'required|integer|min:1',
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
            'duration_days' => 'required|integer|min:1',
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
}
