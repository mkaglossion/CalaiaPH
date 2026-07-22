<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\ActivitySwiperImage;
use App\Services\CloudinaryImageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ActivitySwiperImageController extends Controller
{
    public function __construct(private readonly CloudinaryImageService $cloudinaryImages)
    {
    }

    public function index()
    {
        $images = ActivitySwiperImage::with('activity')->latest()->get(); // eager load activity
        return view('admin.activity-swipers', compact('images'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'activity_id' => 'required|exists:activities,id',
            'image' => 'required|file|mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv|max:102400',
        ]);

        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');

        try {
            $uploadResult = $this->cloudinaryImages->upload($file, 'activity-swipers');
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Image upload failed: ' . $e->getMessage(),
                'errors' => ['image' => ['Failed to upload file to Cloudinary.']],
            ], 422);
        }

        ActivitySwiperImage::create([
            'activity_id' => $request->activity_id,
            'title' => $request->title ?? null,
            'image_path' => $uploadResult['url'],
            'public_id' => $uploadResult['public_id'],
        ]);

        $message = $isVideo ? 'Video uploaded successfully!' : 'Image uploaded successfully!';

        // Return JSON if request expects JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'image_path' => $uploadResult['url'],
                'public_id' => $uploadResult['public_id'],
            ]);
        }

        // Fallback for normal form submission
        return back()->with('success', $message);
    }

    public function update(Request $request, ActivitySwiperImage $activity_image)
    {
        $request->validate([
            'activity_id' => 'required|exists:activities,id',
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|file|mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv|max:102400',
        ]);

        $data = [
            'activity_id' => $request->activity_id,
            'title' => $request->title ?? null,
        ];

        if ($request->hasFile('image')) {
            // Delete old image from Cloudinary
            if ($activity_image->public_id) {
                $this->cloudinaryImages->destroy($activity_image->public_id);
            }

            // Upload new image to Cloudinary
            try {
                $uploadResult = $this->cloudinaryImages->upload($request->file('image'), 'activity-swipers');
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => 'Image upload failed: ' . $e->getMessage(),
                    'errors' => ['image' => ['Failed to upload file to Cloudinary.']],
                ], 422);
            }

            $data['image_path'] = $uploadResult['url'];
            $data['public_id'] = $uploadResult['public_id'];
        }

        $activity_image->update($data);

        $message = $request->hasFile('image') ? 'Image updated successfully!' : 'Image details updated successfully!';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function destroy(ActivitySwiperImage $activity_image)
    {
        Log::info('Deleting image', $activity_image->toArray());

        // Delete from Cloudinary if public_id exists
        if (!empty($activity_image->public_id)) {
            try {
                $this->cloudinaryImages->destroy($activity_image->public_id);
                Log::info('✅ Deleted image from Cloudinary', [
                    'public_id' => $activity_image->public_id
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Failed to delete from Cloudinary', [
                    'public_id' => $activity_image->public_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Delete local file if exists (optional, remove if storing only in Cloudinary)
        $path = $activity_image->image_path;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            Log::info('✅ Deleted local file', ['path' => $path]);
        } else {
            Log::warning('⚠️ Local file not found or path null', ['path' => $path]);
        }

        // Delete DB record
        $activity_image->delete();

        return back()->with('success', 'Image deleted successfully!');
    }




}
