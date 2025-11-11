<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staycation;
use App\Models\StaycationSwiperImage;
use Illuminate\Support\Facades\Storage;

class StaycationSwiperImageController extends Controller
{
    public function index()
    {
        $images = StaycationSwiperImage::with('staycation')->latest()->get(); // eager load staycation
        return view('admin.staycation-swipers', compact('images'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staycation_id' => 'required|exists:staycations,id',
            'image' => 'required|image|max:20480',
        ]);

        $path = $request->file('image')->store('staycations', 'public');

        StaycationSwiperImage::create([
            'staycation_id' => $request->staycation_id,
            'title' => $request->title ?? null,
            'image_path' => $path,
        ]);

        // Return JSON if request expects JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Image uploaded successfully!',
                'image_path' => $path
            ]);
        }

        // Fallback for normal form submission
        return back()->with('success', 'Image uploaded successfully!');
    }

    public function destroy(StaycationSwiperImage $staycation_image)
    {
        \Log::info('Deleting image', $staycation_image->toArray());

        $path = $staycation_image->image_path;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            \Log::info('✅ Deleted file successfully', ['path' => $path]);
        } else {
            \Log::warning('⚠️ File not found or path null', ['path' => $path]);
        }

        $staycation_image->delete();

        return back()->with('success', 'Image deleted successfully!');
    }




}
