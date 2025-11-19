<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SwiperImage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class SwiperImageController extends Controller
{
    public function index()
    {
        $images = SwiperImage::all();
        return view('admin.pages.home', compact('images'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|max:20480', // max 20MB
            'title' => 'nullable|string|max:255',
        ]);

        // Upload image to Cloudinary
        $filePath = $request->file('image')->getRealPath();
        $uploadResult = Cloudinary::uploadApi()->upload($filePath);

        $uploadedFileUrl = $uploadResult['secure_url']; // Cloudinary secure URL
        $publicId = $uploadResult['public_id']; // Store this if you want to delete later

        // Save to DB
        SwiperImage::create([
            'title' => $validated['title'] ?? null,
            'image_path' => $uploadedFileUrl, // store Cloudinary URL
            'public_id' => $publicId, // optional column in your DB for deletion
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully!',
            'url' => $uploadedFileUrl
        ]);
    }

    public function destroy(SwiperImage $swiperImage)
    {
        // Delete from Cloudinary if public_id is stored
        if ($swiperImage->public_id ?? false) {
            Cloudinary::uploadApi()->destroy($swiperImage->public_id);
        }

        $swiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
