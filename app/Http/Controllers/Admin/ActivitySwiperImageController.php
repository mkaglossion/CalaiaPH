<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\ActivitySwiperImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ActivitySwiperImageController extends Controller
{
    public function index()
    {
        $images = ActivitySwiperImage::with('activity')->latest()->get(); // eager load activity
        return view('admin.activity-swipers', compact('images'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'activity_id' => 'required|exists:activities,id',
            'image' => 'required|file|mimes:mp4,avi,mov,wmv,jpeg,png,jpg,gif,webp|max:102400',
        ]);

        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');

        $path = $file->store('activities', 'public');

        ActivitySwiperImage::create([
            'activity_id' => $request->activity_id,
            'title' => $request->title ?? null,
            'image_path' => $path,
        ]);

        $message = $isVideo ? 'Video uploaded successfully!' : 'Image uploaded successfully!';

        // Return JSON if request expects JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'image_path' => $path
            ]);
        }

        // Fallback for normal form submission
        return back()->with('success', $message);
    }

    public function destroy(ActivitySwiperImage $activity_image)
    {
        Log::info('Deleting image', $activity_image->toArray());

        $path = $activity_image->image_path;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            Log::info('✅ Deleted file successfully', ['path' => $path]);
        } else {
            Log::warning('⚠️ File not found or path null', ['path' => $path]);
        }

        $activity_image->delete();

        return back()->with('success', 'Image deleted successfully!');
    }




}
