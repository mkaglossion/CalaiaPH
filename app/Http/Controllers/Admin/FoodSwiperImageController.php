<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FoodSwiperImage;
use Illuminate\Support\Facades\Storage;
use App\Facades\Cloudinary;


class FoodSwiperImageController extends Controller
{
    public function index()
    {
        $images = FoodSwiperImage::all();
        return view('admin.pages.wheretostay', compact('images'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|file|mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv|max:102400',
            'title' => 'nullable|string|max:255',
            'food_id' => 'nullable|exists:foods,id',
        ]);

        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');

        // Upload to Cloudinary
        $filePath = $file->getRealPath();
        $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

        $uploadedFileUrl = $uploadResult['secure_url']; // Cloudinary secure URL
        $publicId = $uploadResult['public_id']; // Store this if you want to delete later

        // Get max sort_order for this food or global
        $maxSortOrder = FoodSwiperImage::max('sort_order') ?? 0;

        // Save to DB
        $data = [
            'title' => $validated['title'] ?? null,
            'image_path' => $uploadedFileUrl, // store Cloudinary URL
            'public_id' => $publicId,
            'sort_order' => $maxSortOrder + 1,
        ];

        // Only add food_id if provided
        if (!empty($validated['food_id'])) {
            $data['food_id'] = $validated['food_id'];
        }

        $image = FoodSwiperImage::create($data);

        $message = $isVideo ? 'Video uploaded successfully!' : 'Image uploaded successfully!';

        return response()->json([
            'message' => $message,
            'image' => $image->fresh(),
        ]);
    }

    public function update(Request $request, FoodSwiperImage $foodSwiperImage)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|file|mimes:mp4,avi,mov,wmv,jpeg,png,jpg,gif,webp|max:102400',
            'food_id' => 'nullable|exists:foods,id',
        ]);

        $data = [
            'title' => $validated['title'] ?? null
        ];

        // Only add food_id if provided
        if (!empty($validated['food_id'])) {
            $data['food_id'] = $validated['food_id'];
        }

        if ($request->hasFile('image')) {
            // Delete old image from Cloudinary
            if ($foodSwiperImage->public_id) {
                Cloudinary::uploadApi()->destroy($foodSwiperImage->public_id);
            }

            // Upload new image to Cloudinary
            $file = $request->file('image');
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

            $data['image_path'] = $uploadResult['secure_url'];
            $data['public_id'] = $uploadResult['public_id'];
        }

        $foodSwiperImage->update($data);
        $updatedImage = $foodSwiperImage->fresh();

        $message = $request->hasFile('image') ? 'Image updated successfully!' : 'Image details updated successfully!';

        return response()->json([
            'message' => $message,
            'image' => $updatedImage,
        ]);
    }


    public function destroy(Request $request, FoodSwiperImage $FoodSwiperImage)
    {
        if ($FoodSwiperImage->public_id ?? false) {
            Cloudinary::uploadApi()->destroy($FoodSwiperImage->public_id);
        }

        $FoodSwiperImage->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Image deleted successfully!']);
        }

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
