<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopSwiperImage;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;

class ShopSwiperImageController extends Controller
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|file|mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv|max:102400',
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');

        try {
            // Upload to Cloudinary
            $uploadedFile = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => 'CalaiaPH/shop-swipers',
                    'resource_type' => 'auto',
                ]
            );

            // Create record
            ShopSwiperImage::create([
                'title' => $validated['title'] ?? null,
                'image_path' => $uploadedFile['secure_url'],
                'public_id' => $uploadedFile['public_id'],
            ]);

            $message = $isVideo ? 'Video uploaded successfully!' : 'Image uploaded successfully!';

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $image = ShopSwiperImage::findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:255',
        ]);

        $image->update([
            'title' => $request->input('title', $image->title),
        ]);

        return response()->json(['success' => true, 'message' => 'Image updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $image = ShopSwiperImage::findOrFail($id);

        try {
            // Delete from Cloudinary
            if ($image->public_id) {
                $this->cloudinary->uploadApi()->destroy($image->public_id);
            }

            // Delete from database
            $image->delete();

            return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete multiple images
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        try {
            foreach ($ids as $id) {
                $image = ShopSwiperImage::find($id);
                if ($image) {
                    if ($image->public_id) {
                        $this->cloudinary->uploadApi()->destroy($image->public_id);
                    }
                    $image->delete();
                }
            }

            return response()->json(['success' => true, 'message' => 'Images deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
