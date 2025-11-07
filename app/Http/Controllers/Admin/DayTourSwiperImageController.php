<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DayTourSwiperImage;
use Illuminate\Support\Facades\Storage;

class DaytourSwiperImageController extends Controller
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

        // Store the image
        $path = $request->file('image')->store('swiper_images', 'public');

        // Save to DB
        DayTourSwiperImage::create([
            'title' => $validated['title'] ?? null,
            'image_path' => $path,
        ]);

        return response()->json(['message' => 'Image uploaded successfully!']);
    }


    public function destroy(DayTourSwiperImage $DayTourSwiperImage)
    {
        Storage::disk('public')->delete($DayTourSwiperImage->image_path);
        $DayTourSwiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
