<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class FarmExperiencesApiController extends Controller
{
    /**
     * Upload image to Cloudinary
     */
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:102400', // max 100MB
            ]);

            if (!$request->hasFile('image')) {
                return response()->json(['success' => false, 'message' => 'No image provided'], 400);
            }

            $file = $request->file('image');
            $filePath = $file->getRealPath();

            try {
                $uploadResult = Cloudinary::uploadApi()->upload($filePath, [
                    'folder' => config('cloudinary.upload.folder') ?: 'farm_experiences',
                    'resource_type' => 'auto'
                ]);
                return response()->json([
                    'success' => true,
                    'imageUrl' => $uploadResult['secure_url'],
                    'public_id' => $uploadResult['public_id'],
                    'message' => 'Image uploaded successfully'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error uploading image: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}
