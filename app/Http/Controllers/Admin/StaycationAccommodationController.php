<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staycation;
use App\Models\StaycationAccommodation;
use App\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

class StaycationAccommodationController extends Controller
{
    public function index()
    {
        $accommodations = StaycationAccommodation::with('variants')->get(); // use variants
        $staycations = Staycation::all();
        return view('admin.accommodation', compact('accommodations', 'staycations'));
    }

    public function store(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:staycation_accommodations,name',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'staycation_ids' => 'nullable|array',
            'staycation_ids.*' => 'exists:staycations,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
        ]);

        $imageUrl = null;
        $publicId = null;

        // Upload image if exists
        if ($request->hasFile('image')) {
            try {
                $filePath = $request->file('image')->getRealPath();
                $uploadResult = Cloudinary::uploadApi()->upload($filePath, [
                    'folder' => 'staycation_accommodations',
                ]);

                $imageUrl = $uploadResult['secure_url'];
                $publicId = $uploadResult['public_id'];
            } catch (\Exception $e) {
                // Log the error but continue without image
                Log::error('Cloudinary upload failed: ' . $e->getMessage());
            }
        }

        // Create accommodation
        $accommodation = StaycationAccommodation::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? null,
            'image_path' => $imageUrl,
            'public_id' => $publicId,
        ]);

        // Sync related staycations
        // $accommodation->accommodationVariants()->sync($request->staycation_ids ?? []);
        $accommodation->variants()->sync($request->staycation_ids ?? []);
        

        return $request->wantsJson() || $request->ajax()
            ? response()->json([
                'message' => 'Accommodation created!',
                'accommodation' => $accommodation->load('variants'),
            ])
            : back()->with('success', 'Accommodation created successfully!');
    }

    public function update(Request $request, StaycationAccommodation $staycationAccommodation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:staycation_accommodations,name,' . $staycationAccommodation->id,
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'staycation_ids' => 'nullable|array',
            'staycation_ids.*' => 'exists:staycations,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
        ]);

        // Handle new image
        if ($request->hasFile('image')) {
            try {
                $filePath = $request->file('image')->getRealPath();
                $uploadResult = Cloudinary::uploadApi()->upload($filePath, [
                    'folder' => 'staycation_accommodations',
                ]);

                // Delete old image from Cloudinary
                if ($staycationAccommodation->public_id) {
                    Cloudinary::uploadApi()->destroy($staycationAccommodation->public_id);
                }

                $staycationAccommodation->image_path = $uploadResult['secure_url'];
                $staycationAccommodation->public_id = $uploadResult['public_id'];
            } catch (\Exception $e) {
                Log::error('Cloudinary upload failed: ' . $e->getMessage());
            }
        }

        // Update other fields
        $staycationAccommodation->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? null,
        ]);

        // Sync related staycations
        // $staycationAccommodation->accommodationVariants()->sync($request->staycation_ids ?? []);
        $staycationAccommodation->variants()->sync($request->staycation_ids ?? []);


        return $request->wantsJson() || $request->ajax()
            ? response()->json([
                'message' => 'Accommodation updated successfully!',
                'accommodation' => $staycationAccommodation->load('variants'),
            ])
            : back()->with('success', 'Day accommodation updated!');

    }

    public function destroy(StaycationAccommodation $staycationAccommodation)
    {
        // Optional: delete image from Cloudinary
        if ($staycationAccommodation->public_id) {
            try {
                Cloudinary::uploadApi()->destroy($staycationAccommodation->public_id);
            } catch (\Exception $e) {
                Log::error('Cloudinary delete failed: ' . $e->getMessage());
            }
        }

        $staycationAccommodation->delete();

        return back()->with('success', 'Day accommodation deleted!');
    }
}
