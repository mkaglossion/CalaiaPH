<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FarmSwiperImage;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'required|image|max:20480',
            'title' => 'nullable|string|max:255',
        ]);

        // Store the image
        $path = $request->file('image')->store('swiper_images', 'public');

        // Save to DB
        FarmSwiperImage::create([
            'title' => $validated['title'] ?? null,
            'image_path' => $path,
        ]);

        return response()->json(['message' => 'Image uploaded successfully!']);
    }


    public function destroy(FarmSwiperImage $FarmSwiperImage)
    {
        Storage::disk('public')->delete($FarmSwiperImage->image_path);
        $FarmSwiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
