<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventSwiperImage;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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

         // Upload image to Cloudinary
        $filePath = $request->file('image')->getRealPath();
        $uploadResult = Cloudinary::uploadApi()->upload($filePath);

        $uploadedFileUrl = $uploadResult['secure_url']; // Cloudinary secure URL
        $publicId = $uploadResult['public_id'];

        EventSwiperImage::create([
            'event_id' => $request->event_id,
            'title' => $request->title ?? null,
            'image_path' =>  $uploadedFileUrl,
            'public_id' => $publicId,
        ]);

        // Return JSON if request expects JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Image uploaded successfully!',
                'image_path' => $uploadedFileUrl
            ]);
        }

        // Fallback for normal form submission
        return back()->with('success', 'Image uploaded successfully!');
    }

    public function destroy(EventSwiperImage $event_image)
    {
        \Log::info('Deleting image', $event_image->toArray());

        // Delete from Cloudinary if public_id exists
        if (!empty($event_image->public_id)) {
            try {
                Cloudinary::uploadApi()->destroy($event_image->public_id);
                \Log::info('✅ Deleted image from Cloudinary', [
                    'public_id' => $event_image->public_id
                ]);
            } catch (\Exception $e) {
                \Log::error('❌ Failed to delete from Cloudinary', [
                    'public_id' => $event_image->public_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Delete local file if exists (optional, remove if storing only in Cloudinary)
        $path = $event_image->image_path;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            \Log::info('✅ Deleted local file', ['path' => $path]);
        } else {
            \Log::warning('⚠️ Local file not found or path null', ['path' => $path]);
        }

        // Delete DB record
        $event_image->delete();

        return back()->with('success', 'Image deleted successfully!');
    }




}
