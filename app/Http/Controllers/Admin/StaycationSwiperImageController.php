<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staycation;
use App\Models\StaycationSwiperImage;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


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

         // Upload image to Cloudinary
        $filePath = $request->file('image')->getRealPath();
        $uploadResult = Cloudinary::uploadApi()->upload($filePath);

        $uploadedFileUrl = $uploadResult['secure_url']; // Cloudinary secure URL
        $publicId = $uploadResult['public_id']; // Store this if you want to delete later

        StaycationSwiperImage::create([
            'staycation_id' => $request->staycation_id,
            'title' => $request->title ?? null,
            'image_path' => $uploadedFileUrl,
            'public_id' => $publicId, // optional column in your DB for deletion
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

    public function destroy(StaycationSwiperImage $staycation_image)
    {
        \Log::info('Deleting image', $staycation_image->toArray());

        // Delete from Cloudinary if public_id exists
        if (!empty($staycation_image->public_id)) {
            try {
                Cloudinary::uploadApi()->destroy($staycation_image->public_id);
                \Log::info('✅ Deleted image from Cloudinary', [
                    'public_id' => $staycation_image->public_id
                ]);
            } catch (\Exception $e) {
                \Log::error('❌ Failed to delete from Cloudinary', [
                    'public_id' => $staycation_image->public_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Delete local file if exists (optional, remove if storing only in Cloudinary)
        $path = $staycation_image->image_path;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            \Log::info('✅ Deleted local file', ['path' => $path]);
        } else {
            \Log::warning('⚠️ Local file not found or path null', ['path' => $path]);
        }

        // Delete DB record
        $staycation_image->delete();

        return back()->with('success', 'Image deleted successfully!');
    }





}
