<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staycation;
use App\Models\StaycationSwiperImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Facades\Cloudinary;


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
            'image' => 'required|file|mimes:mp4,avi,mov,wmv,jpeg,png,jpg,gif,webp|max:102400',
        ]);

        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');
        $resourceType = $isVideo ? 'video' : 'image';

        // Upload to Cloudinary
        $filePath = $file->getRealPath();
        $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

        $uploadedFileUrl = $uploadResult['secure_url']; // Cloudinary secure URL
        $publicId = $uploadResult['public_id']; // Store this if you want to delete later

        // Get staycation for default title
        $staycation = Staycation::find($request->staycation_id);
        $title = $request->title ?? $staycation->name;

        StaycationSwiperImage::create([
            'staycation_id' => $request->staycation_id,
            'title' => $title,
            'image_path' => $uploadedFileUrl,
            'public_id' => $publicId,
        ]);

        $message = $isVideo ? 'Video uploaded successfully!' : 'Image uploaded successfully!';

        // Return JSON if request expects JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'image_path' => $uploadedFileUrl, // store Cloudinary URL
                'public_id' => $publicId,
            ]);
        }

        // Fallback for normal form submission
        return back()->with('success', $message);
    }

    public function update(Request $request, StaycationSwiperImage $staycation_image)
    {
        $request->validate([
            'staycation_id' => 'required|exists:staycations,id',
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|file|mimes:mp4,avi,mov,wmv,jpeg,png,jpg,gif,webp|max:102400',
        ]);

        // Get staycation for default title
        $staycation = Staycation::find($request->staycation_id);
        $title = $request->title ?? $staycation->name;

        $data = [
            'staycation_id' => $request->staycation_id,
            'title' => $title,
        ];

        if ($request->hasFile('image')) {
            // Delete old image from Cloudinary
            if ($staycation_image->public_id) {
                Cloudinary::uploadApi()->destroy($staycation_image->public_id);
            }

            // Upload new image to Cloudinary
            $file = $request->file('image');
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

            $data['image_path'] = $uploadResult['secure_url'];
            $data['public_id'] = $uploadResult['public_id'];
        }

        $staycation_image->update($data);

        $message = $request->hasFile('image') ? 'Image updated successfully!' : 'Image details updated successfully!';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function destroy(StaycationSwiperImage $staycation_image)
    {
        Log::info('Deleting image', $staycation_image->toArray());

        // Delete from Cloudinary if public_id exists
        if (!empty($staycation_image->public_id)) {
            try {
                Cloudinary::uploadApi()->destroy($staycation_image->public_id);
                Log::info('✅ Deleted image from Cloudinary', [
                    'public_id' => $staycation_image->public_id
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Failed to delete from Cloudinary', [
                    'public_id' => $staycation_image->public_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Delete local file if exists (optional, remove if storing only in Cloudinary)
        $path = $staycation_image->image_path;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            Log::info('✅ Deleted local file', ['path' => $path]);
        } else {
            Log::warning('⚠️ Local file not found or path null', ['path' => $path]);
        }

        // Delete DB record
        $staycation_image->delete();

        return back()->with('success', 'Image deleted successfully!');
    }




}
