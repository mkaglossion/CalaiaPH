<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FarmInfo;
use App\Models\FarmInfoSwiperImage;
use Illuminate\Support\Facades\Storage;

class FarmInfoSwiperImageController extends Controller
{
    public function index()
    {
        $images = FarmInfoSwiperImage::with('farmInfo')->latest()->get(); // eager load farmInfo
        return view('admin.farmInfo-swipers', compact('images'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'farm_info_id' => 'required|exists:farm_infos,id',
            'image' => 'required|image|max:20480',
        ]);

        $path = $request->file('image')->store('farm_infos', 'public');

        FarmInfoSwiperImage::create([
            'farm_info_id' => $request->farm_info_id,
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

    public function destroy(FarmInfoSwiperImage $farmInfo_image)
    {
        \Log::info('Deleting image', $farmInfo_image->toArray());

        $path = $farmInfo_image->image_path;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            \Log::info('✅ Deleted file successfully', ['path' => $path]);
        } else {
            \Log::warning('⚠️ File not found or path null', ['path' => $path]);
        }

        $farmInfo_image->delete();

        return back()->with('success', 'Image deleted successfully!');
    }




}
