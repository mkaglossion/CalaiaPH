<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SpecialSwiperImage;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


class SpecialSwiperImageController extends Controller
{
    public function index()
    {
        $images = SpecialSwiperImage::all();
        return view('admin.pages.wheretostay', compact('images'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|file|mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv|max:102400',
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');

         // Upload to Cloudinary
        $filePath = $file->getRealPath();
        $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

        $uploadedFileUrl = $uploadResult['secure_url']; // Cloudinary secure URL
        $publicId = $uploadResult['public_id']; // Store this if you want to delete later


        // Save to DB
        $image = SpecialSwiperImage::create([
            'title' => $validated['title'] ?? null,
            'image_path' => $uploadedFileUrl,
            'public_id' => $publicId, // optional column in your DB for deletion
        ]);

        $message = $isVideo ? 'Video uploaded successfully!' : 'Image uploaded successfully!';

        return response()->json([
            'message' => $message,
            'image' => $image->fresh(),
        ]);
    }

    public function update(Request $request, SpecialSwiperImage $specialSwiperImage)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|file|mimes:mp4,avi,mov,wmv,jpeg,png,jpg,gif,webp|max:102400',
        ]);

        $data = ['title' => $validated['title'] ?? null];

        if ($request->hasFile('image')) {
            // Delete old image from Cloudinary
            if ($specialSwiperImage->public_id) {
                Cloudinary::uploadApi()->destroy($specialSwiperImage->public_id);
            }

            // Upload new image to Cloudinary
            $file = $request->file('image');
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

            $data['image_path'] = $uploadResult['secure_url'];
            $data['public_id'] = $uploadResult['public_id'];
        }

        $specialSwiperImage->update($data);
        $updatedImage = $specialSwiperImage->fresh();

        $message = $request->hasFile('image') ? 'Image updated successfully!' : 'Image details updated successfully!';

        return response()->json([
            'message' => $message,
            'image' => $updatedImage,
        ]);
    }


    public function destroy(Request $request, SpecialSwiperImage $SpecialSwiperImage)
    {
        if ($SpecialSwiperImage->public_id ?? false) {
            Cloudinary::uploadApi()->destroy($SpecialSwiperImage->public_id);
        }        
        $SpecialSwiperImage->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Image deleted successfully!']);
        }

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
