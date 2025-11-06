<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FoodSwiperImage;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'required|image|max:5120',
            'title' => 'nullable|string|max:255',
        ]);

        // Store the image
        $path = $request->file('image')->store('swiper_images', 'public');

        // Save to DB
        FoodSwiperImage::create([
            'title' => $validated['title'] ?? null,
            'image_path' => $path,
        ]);

        return response()->json(['message' => 'Image uploaded successfully!']);
    }


    public function destroy(FoodSwiperImage $FoodSwiperImage)
    {
        Storage::disk('public')->delete($FoodSwiperImage->image_path);
        $FoodSwiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
