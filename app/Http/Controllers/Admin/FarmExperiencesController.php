<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

class FarmExperiencesController extends Controller
{
    /**
     * Show the farm experiences management page
     */
    public function index()
    {
        $experiences = $this->getFarmExperiences();
        return view('admin.ourFarm.experiences', compact('experiences'));
    }

    /**
     * Get farm experiences from cache
     */
    protected function getFarmExperiences()
    {
        $defaultExperiences = [
            [
                'id' => 1,
                'img' => null,
                'title' => 'Animal Feeding Experience',
                'desc' => 'Get up close with our farm animals. Learn about their care and dietary needs while you feed them.',
                'time' => 'Weekends 9AM–2PM',
                'capacity' => 'Groups up to 20 people',
                'icon' => 'people',
                'cta' => 'Reserve Your Spot',
                'badge' => null,
                'created_at' => '2024-11-27 10:30:00'
            ],
            [
                'id' => 2,
                'img' => null,
                'title' => 'Harvest Days',
                'desc' => 'Participate in seasonal harvesting. Learn sustainable farming practices and take home fresh produce.',
                'time' => 'Morning hours seasonal',
                'capacity' => 'Take home harvest',
                'icon' => 'flower1',
                'cta' => 'Learn More',
                'badge' => null,
                'created_at' => '2024-11-28 14:15:00'
            ],
            [
                'id' => 3,
                'img' => null,
                'title' => 'Farm Tours',
                'desc' => 'Explore our farm and learn about sustainable agriculture, animal welfare, and eco-friendly practices.',
                'time' => 'By appointment',
                'capacity' => 'Educational experience',
                'icon' => 'mortarboard',
                'cta' => 'Schedule Tour',
                'badge' => 'Guided Tour',
                'created_at' => '2024-11-29 09:45:00'
            ]
        ];

        $cached = Cache::get('farm_experiences', null);
        
        if (!$cached) {
            Cache::forever('farm_experiences', $defaultExperiences);
            return $defaultExperiences;
        }

        // Ensure all cached items have created_at
        $cached = array_map(function($item) {
            if (!isset($item['created_at']) || empty($item['created_at'])) {
                $item['created_at'] = now()->toDateTimeString();
            }
            return $item;
        }, $cached);

        return $cached;
    }

    /**
     * Save farm experiences via AJAX
     */
    public function save(Request $request)
    {
        try {
            $experiences = $request->get('experiences');

            if (!$experiences) {
                return response()->json(['message' => 'Invalid request', 'success' => false], 400);
            }

            // Save to cache (persistent across requests)
            Cache::forever('farm_experiences', $experiences);

            return response()->json([
                'message' => 'Experiences saved successfully',
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error saving experiences: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * Upload image to Cloudinary for a specific experience
     */
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            ]);

            if (!$request->hasFile('image')) {
                return response()->json(['message' => 'No image provided', 'success' => false], 400);
            }

            // Upload to Cloudinary
            $filePath = $request->file('image')->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, [
                'folder' => env('CLOUDINARY_UPLOAD_FOLDER', 'farm_experiences'),
            ]);

            $imageUrl = $uploadResult['secure_url'];

            return response()->json([
                'success' => true,
                'imageUrl' => $imageUrl,
                'message' => 'Image uploaded successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Cloudinary upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading image: ' . $e->getMessage()
            ], 500);
        }
    }
}
