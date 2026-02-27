<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class TestimonialController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'content' => 'required|string',
            'image_path' => 'nullable|string|max:255',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'order' => 'nullable|integer',
            'rating' => 'nullable|integer|min:1|max:5'
        ]);

        $imagePath = $request->image_path ?: '';

        // Handle file upload to Cloudinary
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
            $imagePath = $uploadResult['secure_url'];
        }

        Testimonial::create([
            'name' => $request->name,
            'position' => $request->position,
            'content' => $request->content,
            'image_path' => $imagePath,
            'order' => $request->order ?? 0,
            'status' => 'approved',
            'rating' => $request->rating ?? 5
        ]);

        return response()->json(['message' => 'Testimonial added successfully.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::find($id);
        
        if (!$testimonial) {
            return response()->json(['message' => 'Testimonial not found.'], 404);
        }

        Log::info('Update testimonial called', [
            'testimonial_id' => $testimonial->id,
            'request_data' => $request->all()
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'content' => 'required|string',
            'image_path' => 'nullable|string|max:255',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'remove_image' => 'nullable|in:1',
            'order' => 'nullable|integer',
            'rating' => 'nullable|integer|min:1|max:5'
        ]);

        Log::info('Validation passed', ['validated' => $validated]);

        $data = [
            'name' => $request->name,
            'position' => $request->position,
            'content' => $request->content,
            'order' => $request->order ?? 0,
            'rating' => $request->rating ?? 5
        ];

        // Handle image operations
        if ($request->remove_image) {
            $data['image_path'] = '';
        } elseif ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
            $data['image_path'] = $uploadResult['secure_url'];
        } else {
            $data['image_path'] = $request->image_path ?: $testimonial->image_path;
        }

        Log::info('Data to update', ['data' => $data]);

        $updated = $testimonial->update($data);

        $fresh = $testimonial->fresh();
        Log::info('Update result', ['updated' => $updated, 'testimonial_after' => $fresh ? $fresh->toArray() : null]);

        return response()->json([
            'message' => 'Testimonial updated successfully.',
            'testimonial' => $fresh
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $testimonial = Testimonial::find($id);
        
        if (!$testimonial) {
            return response()->json(['message' => 'Testimonial not found.'], 404);
        }

        $testimonial->delete();

        return response()->json(['message' => 'Testimonial deleted successfully.']);
    }
}
