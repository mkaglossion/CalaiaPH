<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FarmSwiperImage;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class FarmSwiperImageController extends Controller
{
    public function index()
    {
        $images = FarmSwiperImage::all();
        return view('admin.pages.ourfarm', compact('images'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|file|mimes:mp4,avi,mov,wmv,jpeg,png,jpg,gif,webp|max:102400',
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');
        $resourceType = $isVideo ? 'video' : 'image';

         // Upload to Cloudinary
        $filePath = $file->getRealPath();
        $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

        $uploadedFileUrl = $uploadResult['secure_url']; // Cloudinary secure URL
        $publicId = $uploadResult['public_id']; // Store this if you want to delete later

        // Save to DB
        FarmSwiperImage::create([
            'title' => $validated['title'] ?? null,
            'image_path' =>  $uploadedFileUrl, // store Cloudinary URL
            'public_id' => $publicId,
        ]);

        $message = $isVideo ? 'Video uploaded successfully!' : 'Image uploaded successfully!';

        return response()->json(['message' => $message]);
    }


    public function destroy(FarmSwiperImage $FarmSwiperImage)
    {
        if ($FarmSwiperImage->public_id ?? false) {
            Cloudinary::uploadApi()->destroy($FarmSwiperImage->public_id);
        }
        $FarmSwiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
