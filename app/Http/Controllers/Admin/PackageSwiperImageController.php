<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PackageSwiperImage;
use Illuminate\Support\Facades\DB;
use App\Facades\Cloudinary;

class PackageSwiperImageController extends Controller
{
    public function index()
    {
        $images = PackageSwiperImage::ordered()->get();
        return view('admin.packages.packagesInfos', compact('images'));
    }

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
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);

            $uploadedFileUrl = $uploadResult['secure_url'];
            $publicId = $uploadResult['public_id'];

            // Save to DB
            $image = PackageSwiperImage::create([
                'title' => $validated['title'] ?? null,
                'image_path' => $uploadedFileUrl,
                'public_id' => $publicId,
            ]);
            $image->refresh();

            $message = $isVideo ? 'Video uploaded successfully!' : 'Image uploaded successfully!';

            return response()->json([
                'message' => $message,
                'image' => $this->imagePayload($image),
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
        $packageSwiperImage->refresh();

        $message = $request->hasFile('image') ? 'Image updated successfully!' : 'Image details updated successfully!';

        return response()->json([
            'message' => $message,
            'image' => $this->imagePayload($packageSwiperImage),
        ]);
    }

    public function destroy(PackageSwiperImage $packageSwiperImage)
    {
        // Delete from Cloudinary if public_id is stored
        if ($packageSwiperImage->public_id ?? false) {
            Cloudinary::uploadApi()->destroy($packageSwiperImage->public_id);
        }

        $packageSwiperImage->delete();

        return response()->json([
            'message' => 'Image deleted successfully!',
            'id' => $packageSwiperImage->id,
        ]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer', 'exists:package_swiper_images,id'],
        ]);

        $images = PackageSwiperImage::ordered()->get();
        $currentIds = $images->pluck('id')->map(fn ($id) => (int) $id)->all();
        $orderedIds = array_values(array_unique(array_map('intval', $validated['ordered_ids'])));
        $orderedIds = array_values(array_intersect($orderedIds, $currentIds));
        $finalIds = array_merge($orderedIds, array_values(array_diff($currentIds, $orderedIds)));

        DB::transaction(function () use ($finalIds): void {
            foreach ($finalIds as $index => $id) {
                PackageSwiperImage::whereKey($id)->update([
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Image order updated successfully.',
        ]);
    }

    private function imagePayload(PackageSwiperImage $image): array
    {
        return [
            'id' => $image->id,
            'title' => $image->title,
            'image_path' => $image->image_path,
            'public_id' => $image->public_id,
            'sort_order' => $image->sort_order,
            'created_at' => optional($image->created_at)->toISOString(),
            'updated_at' => optional($image->updated_at)->toISOString(),
        ];
    }
}
