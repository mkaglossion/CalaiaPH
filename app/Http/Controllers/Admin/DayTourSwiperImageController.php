<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DayTourSwiperImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class DayTourSwiperImageController extends Controller
{
    public function index()
    {
        $images = DayTourSwiperImage::all();
        return view('admin.pages.daytour', compact('images'));
    }

    // Upload additional swiper images for modal (separate from package image)
    public function store(Request $request)
    {
        Log::info('DayTourSwiperImageController::store called', $request->all());
        
        $validated = $request->validate([
            'image' => 'required|file|mimes:mp4,avi,mov,wmv,jpeg,png,jpg,gif,webp|max:102400',
            'title' => 'nullable|string|max:255',
            'daytour_id' => 'nullable|exists:day_tours,id',
        ]);

        Log::info('Validation passed', $validated);

        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');
        $resourceType = $isVideo ? 'video' : 'image';

        try {
            // Upload to Cloudinary
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

            $uploadedFileUrl = $uploadResult['secure_url']; // Cloudinary secure URL
            $publicId = $uploadResult['public_id'];

            // Save to DB
            DayTourSwiperImage::create([
                'title' => $validated['title'] ?? null,
                'image_path' => $uploadedFileUrl, // store Cloudinary URL
                'public_id' => $publicId,
                'daytour_id' => $validated['daytour_id'] ?? null,
            ]);

            $message = $isVideo ? 'Video uploaded successfully!' : 'Image uploaded successfully!';

            return response()->json(['message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }


    public function update(Request $request, DayTourSwiperImage $dayTourSwiperImage)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|file|mimes:mp4,avi,mov,wmv,jpeg,png,jpg,gif,webp|max:102400',
            'daytour_id' => 'nullable|exists:day_tours,id',
        ]);

        $data = [
            'title' => $validated['title'] ?? null,
            'daytour_id' => $validated['daytour_id'] ?? null,
        ];

        if ($request->hasFile('image')) {
            // Delete old image from Cloudinary
            if ($dayTourSwiperImage->public_id) {
                Cloudinary::uploadApi()->destroy($dayTourSwiperImage->public_id);
            }

            // Upload new image to Cloudinary
            $file = $request->file('image');
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

            $data['image_path'] = $uploadResult['secure_url'];
            $data['public_id'] = $uploadResult['public_id'];
        }

        $dayTourSwiperImage->update($data);

        $message = $request->hasFile('image') ? 'Image updated successfully!' : 'Image details updated successfully!';

        return response()->json(['message' => $message]);
    }

    public function destroy(DayTourSwiperImage $DayTourSwiperImage)
    {
        if ($DayTourSwiperImage->public_id ?? false) {
            Cloudinary::uploadApi()->destroy($DayTourSwiperImage->public_id);
        }
        $DayTourSwiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }

    // Get swiper images for modal (additional images, not package image)
    public function getImages($daytourId)
    {
        $images = DayTourSwiperImage::where('daytour_id', $daytourId)->pluck('image_path')->toArray();
        return response()->json($images);
    }
}
