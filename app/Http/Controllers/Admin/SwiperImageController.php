<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SwiperImage;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'required|image|max:20480',
            'title' => 'nullable|string|max:255',
        ]);

        // Store the image
        $path = $request->file('image')->store('swiper_images', 'public');

        // Save to DB
        SwiperImage::create([
            'title' => $validated['title'] ?? null,
            'image_path' => $path,
        ]);

        return response()->json(['message' => 'Image uploaded successfully!']);
    }


    public function destroy(SwiperImage $swiperImage)
    {
        Storage::disk('public')->delete($swiperImage->image_path);
        $swiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
