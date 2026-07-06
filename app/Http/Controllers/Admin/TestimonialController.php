<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Models\TestimonialSectionSetting;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Throwable;

class TestimonialController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'content' => 'required|string',
            'image_path' => 'nullable|string|max:255',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'order' => 'nullable|integer',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $avatarUpload = $this->storeUpload($request->file('image_file'), 'testimonials/avatars');

        $testimonial = Testimonial::create([
            'name' => $validated['name'],
            'position' => $validated['position'],
            'content' => $validated['content'],
            'image_path' => $avatarUpload['url'] ?? ($validated['image_path'] ?? null),
            'avatar' => $avatarUpload['url'] ?? ($validated['image_path'] ?? null),
            'image_public_id' => $avatarUpload['public_id'] ?? null,
            'order' => $validated['order'] ?? 0,
            'status' => 'approved',
            'rating' => $validated['rating'] ?? 5,
        ]);

        return response()->json([
            'message' => 'Testimonial added successfully.',
            'testimonial' => $testimonial->fresh(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::find($id);

        if (!$testimonial) {
            return response()->json(['message' => 'Testimonial not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'content' => 'required|string',
            'image_path' => 'nullable|string|max:255',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'remove_image' => 'nullable|in:1',
            'order' => 'nullable|integer',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $data = [
            'name' => $validated['name'],
            'position' => $validated['position'],
            'content' => $validated['content'],
            'order' => $validated['order'] ?? 0,
            'rating' => $validated['rating'] ?? 5,
        ];

        $currentAvatarPath = $testimonial->image_path ?: $testimonial->avatar;

        if ($request->boolean('remove_image')) {
            $this->destroyUpload($testimonial->image_public_id ?? null);
            $data['image_path'] = null;
            $data['avatar'] = null;
            $data['image_public_id'] = null;
        } elseif ($request->hasFile('image_file')) {
            $this->destroyUpload($testimonial->image_public_id ?? null);
            $avatarUpload = $this->storeUpload($request->file('image_file'), 'testimonials/avatars');
            $data['image_path'] = $avatarUpload['url'] ?? $currentAvatarPath;
            $data['avatar'] = $avatarUpload['url'] ?? $currentAvatarPath;
            $data['image_public_id'] = $avatarUpload['public_id'] ?? null;
        } else {
            $data['image_path'] = $validated['image_path'] ?: $currentAvatarPath;
            $data['avatar'] = $validated['image_path'] ?: $currentAvatarPath;
            $data['image_public_id'] = $testimonial->image_public_id;
        }

        $testimonial->update($data);

        return response()->json([
            'message' => 'Testimonial updated successfully.',
            'testimonial' => $testimonial->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $testimonial = Testimonial::find($id);

        if (!$testimonial) {
            return response()->json(['message' => 'Testimonial not found.'], 404);
        }

        $this->destroyUpload($testimonial->image_public_id ?? null);

        $testimonial->delete();

        return response()->json(['message' => 'Testimonial deleted successfully.']);
    }

    public function updateSectionBackground(Request $request)
    {
        $request->validate([
            'background_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'remove_background_image' => 'nullable|in:1',
        ]);

        $setting = TestimonialSectionSetting::query()->first();
        $hasUpload = $request->hasFile('background_image_file');
        $shouldRemove = $request->boolean('remove_background_image');

        if (!$setting && !$hasUpload && !$shouldRemove) {
            return $this->respondToBackgroundUpdate($request, 'No testimonial background changes were made.');
        }

        if (!$setting) {
            $setting = new TestimonialSectionSetting();
        }

        if ($shouldRemove) {
            $this->destroyUpload($setting->background_image_public_id ?? null);
            $setting->background_image_path = null;
            $setting->background_image_public_id = null;
        } elseif ($hasUpload) {
            $this->destroyUpload($setting->background_image_public_id ?? null);
            $upload = $this->storeUpload($request->file('background_image_file'), 'testimonials/backgrounds');
            $setting->background_image_path = $upload['url'] ?? null;
            $setting->background_image_public_id = $upload['public_id'] ?? null;
        } else {
            return $this->respondToBackgroundUpdate($request, 'No testimonial background changes were made.');
        }

        $setting->save();

        return $this->respondToBackgroundUpdate($request, 'Testimonials section background updated successfully.', $setting);
    }

    private function storeUpload(?UploadedFile $file, string $folder = 'uploads'): array
    {
        if (!$file) {
            return [];
        }

        try {
            $options = [
                'resource_type' => 'image',
            ];

            if ($folder !== '') {
                $options['folder'] = $folder;
            }

            $uploadResult = Cloudinary::uploadApi()->upload($file->getRealPath(), $options);

            return [
                'url' => $uploadResult['secure_url'] ?? null,
                'public_id' => $uploadResult['public_id'] ?? null,
            ];
        } catch (Throwable $e) {
            report($e);
            throw $e;
        }
    }

    private function destroyUpload(?string $publicId): void
    {
        if (empty($publicId)) {
            return;
        }

        try {
            Cloudinary::uploadApi()->destroy($publicId, [
                'resource_type' => 'image',
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function respondToBackgroundUpdate(Request $request, string $message, ?TestimonialSectionSetting $setting = null)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'section_background' => $setting ? [
                    'background_image_path' => $setting->background_image_path,
                    'background_image_public_id' => $setting->background_image_public_id,
                ] : null,
            ]);
        }

        return back()->with('success', $message);
    }
}
