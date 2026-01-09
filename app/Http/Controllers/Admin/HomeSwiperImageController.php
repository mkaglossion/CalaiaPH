<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomeSwiperImage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class HomeSwiperImageController extends Controller
{
    public function index()
    {
        $images = HomeSwiperImage::all();
        return view('admin.pages.home', compact('images'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|file|mimes:mp4,avi,mov,wmv,jpeg,png,jpg,gif,webp|max:102400', // max 100MB
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');

        try {
            // Upload to Cloudinary
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

            $uploadedFileUrl = $uploadResult['secure_url']; // Cloudinary secure URL
            $publicId = $uploadResult['public_id']; // Store this if you want to delete later

            // Save to DB
            HomeSwiperImage::create([
                'title' => $validated['title'] ?? null,
                'image_path' => $uploadedFileUrl, // store Cloudinary URL
                'public_id' => $publicId, // optional column in your DB for deletion
            ]);

            $message = $isVideo ? 'Video uploaded successfully!' : 'Image uploaded successfully!';

            return response()->json([
                'message' => $message,
                'url' => $uploadedFileUrl
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(HomeSwiperImage $homeSwiperImage)
    {
        // Delete from Cloudinary if public_id is stored
        if ($homeSwiperImage->public_id ?? false) {
            Cloudinary::uploadApi()->destroy($homeSwiperImage->public_id);
        }

        $homeSwiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}