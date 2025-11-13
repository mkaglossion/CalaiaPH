<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventSwiperImage;
use Illuminate\Support\Facades\Storage;

class EventSwiperImageController extends Controller
{
    public function index()
    {
        $images = EventSwiperImage::with('event')->latest()->get(); // eager load event
        return view('admin.event-swipers', compact('images'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'image' => 'required|image|max:20480',
        ]);

        $path = $request->file('image')->store('events', 'public');

        EventSwiperImage::create([
            'event_id' => $request->event_id,
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

    public function destroy(EventSwiperImage $event_image)
    {
        \Log::info('Deleting image', $event_image->toArray());

        $path = $event_image->image_path;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            \Log::info('✅ Deleted file successfully', ['path' => $path]);
        } else {
            \Log::warning('⚠️ File not found or path null', ['path' => $path]);
        }

        $event_image->delete();

        return back()->with('success', 'Image deleted successfully!');
    }




}
