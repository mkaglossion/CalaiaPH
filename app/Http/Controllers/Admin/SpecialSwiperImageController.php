<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SpecialSwiperImage;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'required|image|max:20480',
            'title' => 'nullable|string|max:255',
        ]);

        // Store the image
        $path = $request->file('image')->store('swiper_images', 'public');

        // Save to DB
        SpecialSwiperImage::create([
            'title' => $validated['title'] ?? null,
            'image_path' => $path,
        ]);

        return response()->json(['message' => 'Image uploaded successfully!']);
    }


    public function destroy(SpecialSwiperImage $SpecialSwiperImage)
    {
        Storage::disk('public')->delete($SpecialSwiperImage->image_path);
        $SpecialSwiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
