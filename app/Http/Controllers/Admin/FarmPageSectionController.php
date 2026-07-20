<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FarmPageSection;
use App\Services\CloudinaryImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FarmPageSectionController extends Controller
{
    public function update(
        Request $request,
        FarmPageSection $farmPageSection,
        CloudinaryImageService $cloudinary
    ): JsonResponse {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_public_id' => ['nullable', 'string', 'max:255'],
            'secondary_image_url' => ['nullable', 'string', 'max:2048'],
            'secondary_image_public_id' => ['nullable', 'string', 'max:255'],
            'items' => ['present', 'array', 'max:20'],
            'items.*.key' => ['nullable', 'string', 'max:100'],
            'items.*.title' => ['nullable', 'string', 'max:160'],
            'items.*.text' => ['nullable', 'string', 'max:500'],
            'items.*.description' => ['nullable', 'string', 'max:2000'],
            'items.*.meta' => ['nullable', 'string', 'max:160'],
            'items.*.alignment' => ['nullable', 'in:left,right'],
            'items.*.image_url' => ['nullable', 'string', 'max:2048'],
            'items.*.image_public_id' => ['nullable', 'string', 'max:255'],
            'settings' => ['present', 'array'],
            'settings.background_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'is_active' => ['required', 'boolean'],
        ]);

        $oldPublicIds = $farmPageSection->cloudinaryPublicIds();
        $farmPageSection->update($validated);
        $newPublicIds = $farmPageSection->fresh()->cloudinaryPublicIds();

        foreach (array_diff($oldPublicIds, $newPublicIds) as $publicId) {
            try {
                $cloudinary->destroy($publicId);
            } catch (\Throwable $exception) {
                Log::warning('Unable to remove replaced farm section image from Cloudinary.', [
                    'public_id' => $publicId,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Farm section updated successfully.',
            'section' => $farmPageSection->fresh()->adminPayload(),
        ]);
    }

    public function upload(Request $request, CloudinaryImageService $cloudinary): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $uploaded = $cloudinary->upload($validated['image'], 'farm/page-sections');

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded to Cloudinary.',
            'image' => $uploaded,
        ]);
    }
}
