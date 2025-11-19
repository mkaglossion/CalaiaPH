<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DayTourSwiperImage;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class DayTourSwiperImageController extends Controller
{
    public function index()
    {
        $images = DayTourSwiperImage::all();
        return view('admin.pages.daytour', compact('images'));
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
        $publicId = $uploadResult['public_id'];

        // Save to DB
        DayTourSwiperImage::create([
            'title' => $validated['title'] ?? null,
            'image_path' => $uploadedFileUrl, // store Cloudinary URL
            'public_id' => $publicId,
        ]);

        return response()->json(['message' => 'Image uploaded successfully!']);
    }


    public function destroy(DayTourSwiperImage $DayTourSwiperImage)
    {
        if ($DayTourSwiperImage->public_id ?? false) {
            Cloudinary::uploadApi()->destroy($DayTourSwiperImage->public_id);
        }
        $DayTourSwiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
