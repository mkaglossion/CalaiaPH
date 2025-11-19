<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoSwiperImage;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class DoSwiperImageController extends Controller
{
    public function index()
    {
        $images = DoSwiperImage::all();
        return view('admin.pages.wheretostay', compact('images'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|max:20480',
            'title' => 'nullable|string|max:255',
        ]);

        // Upload image to Cloudinary
        $filePath = $request->file('image')->getRealPath();
        $uploadResult = Cloudinary::uploadApi()->upload($filePath);

        $uploadedFileUrl = $uploadResult['secure_url']; // Cloudinary secure URL
        $publicId = $uploadResult['public_id']; // Store this if you want to delete later

        // Save to DB
        DoSwiperImage::create([
            'title' => $validated['title'] ?? null,
            'image_path' => $uploadedFileUrl, // store Cloudinary URL
            'public_id' => $publicId,
        ]);

        return response()->json(['message' => 'Image uploaded successfully!']);
    }


    public function destroy(DoSwiperImage $DoSwiperImage)
    {
        if ($DoSwiperImage->public_id ?? false) {
            Cloudinary::uploadApi()->destroy($DoSwiperImage->public_id);
        }
        $DoSwiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
