<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaySwiperImage;
use Illuminate\Support\Facades\Storage;

class StaySwiperImageController extends Controller
{
    public function index()
    {
        $images = StaySwiperImage::all();
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
        StaySwiperImage::create([
            'title' => $validated['title'] ?? null,
            'image_path' => $uploadedFileUrl, // store Cloudinary URL
            'public_id' => $publicId, // optional column in your DB for deletion
        ]);

        return response()->json(['message' => 'Image uploaded successfully!']);
    }


    public function destroy(StaySwiperImage $StaySwiperImage)
    {
         // Delete from Cloudinary if public_id is stored
        if ($StaySwiperImage->public_id ?? false) {
            Cloudinary::uploadApi()->destroy($StaySwiperImage->public_id);
        }
        $StaySwiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
