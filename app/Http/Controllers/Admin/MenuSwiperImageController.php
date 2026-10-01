<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\MenuSwiperImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


class MenuSwiperImageController extends Controller
{
    public function index()
    {
        $images = MenuSwiperImage::with('menu')->latest()->get(); // eager load menu
        return view('admin.menu-swipers', compact('images'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'image' => 'required|file|mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv|max:102400',
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

        MenuSwiperImage::create([
            'menu_id' => $request->menu_id,
            'title' => $request->title ?? null,
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

    public function destroy(MenuSwiperImage $menu_image)
    {
        Log::info('Deleting image', $menu_image->toArray());

        // Delete from Cloudinary if public_id exists
        if (!empty($menu_image->public_id)) {
            try {
                Cloudinary::uploadApi()->destroy($menu_image->public_id);
                Log::info('✅ Deleted image from Cloudinary', [
                    'public_id' => $menu_image->public_id
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Failed to delete from Cloudinary', [
                    'public_id' => $menu_image->public_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Delete local file if exists (optional, remove if storing only in Cloudinary)
        $path = $menu_image->image_path;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            Log::info('✅ Deleted local file', ['path' => $path]);
        } else {
            Log::warning('⚠️ Local file not found or path null', ['path' => $path]);
        }

        // Delete DB record
        $menu_image->delete();

        return back()->with('success', 'Image deleted successfully!');
    }




}
