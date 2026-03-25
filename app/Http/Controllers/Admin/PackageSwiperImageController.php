<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PackageSwiperImage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class PackageSwiperImageController extends Controller
{
    public function index()
    {
        $images = PackageSwiperImage::all();
        return view('admin.packages.packagesInfos', compact('images'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|file|mimes:mp4,avi,mov,wmv,jpeg,png,jpg,gif,webp|max:102400',
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $isVideo = str_starts_with($mimeType, 'video/');

        try {
            // Upload to Cloudinary
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

            $uploadedFileUrl = $uploadResult['secure_url'];
            $publicId = $uploadResult['public_id'];

            // Save to DB
            PackageSwiperImage::create([
                'title' => $validated['title'] ?? null,
                'image_path' => $uploadedFileUrl,
                'public_id' => $publicId,
            ]);

            $message = $isVideo ? 'Video uploaded successfully!' : 'Image uploaded successfully!';

            return response()->json([
                'message' => $message,
                'url' => $uploadedFileUrl
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, PackageSwiperImage $packageSwiperImage)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|file|mimes:mp4,avi,mov,wmv,jpeg,png,jpg,gif,webp|max:102400',
        ]);

        $data = ['title' => $validated['title'] ?? null];

        if ($request->hasFile('image')) {
            // Delete old image from Cloudinary
            if ($packageSwiperImage->public_id) {
                Cloudinary::uploadApi()->destroy($packageSwiperImage->public_id);
            }

            // Upload new image to Cloudinary
            $file = $request->file('image');
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

            $data['image_path'] = $uploadResult['secure_url'];
            $data['public_id'] = $uploadResult['public_id'];
        }

        $packageSwiperImage->update($data);

        $message = $request->hasFile('image') ? 'Image updated successfully!' : 'Image details updated successfully!';

        return response()->json(['message' => $message]);
    }

    public function destroy(PackageSwiperImage $packageSwiperImage)
    {
        // Delete from Cloudinary if public_id is stored
        if ($packageSwiperImage->public_id ?? false) {
            Cloudinary::uploadApi()->destroy($packageSwiperImage->public_id);
        }

        $packageSwiperImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}

