<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FarmExperience;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FarmExperiencesController extends Controller
{
    /**
     * Show the farm experiences management page
     */
    public function index()
    {
        $experiences = FarmExperience::orderBy('sort_order')->get()->map(function($exp) {
            return [
                'id' => $exp->id,
                'img' => $exp->img,
                'title' => $exp->title,
                'desc' => $exp->desc,
                'time' => $exp->time,
                'capacity' => $exp->capacity,
                'icon' => $exp->icon,
                'cta' => $exp->cta,
                'badge' => $exp->badge,
                'created_at' => $exp->created_at->toDateTimeString(),
            ];
        })->toArray();
        
        return view('admin.ourFarm.experiences', compact('experiences'));
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

            DB::transaction(function () use ($experiences) {
                $keptIds = [];

                foreach ($experiences as $index => $exp) {
                    $payload = [
                        'title' => $exp['title'] ?? '',
                        'desc' => $exp['desc'] ?? '',
                        'time' => $exp['time'] ?? '',
                        'capacity' => $exp['capacity'] ?? '',
                        'icon' => $exp['icon'] ?? 'people',
                        'cta' => $exp['cta'] ?? '',
                        'badge' => $exp['badge'] ?? null,
                        'img' => $exp['img'] ?? null,
                        'sort_order' => $index,
                    ];

                    $experienceId = isset($exp['id']) ? (int) $exp['id'] : null;

                    if ($experienceId && $experienceId > 0) {
                        $model = FarmExperience::updateOrCreate(
                            ['id' => $experienceId],
                            $payload
                        );
                    } else {
                        $model = FarmExperience::create($payload);
                    }

                    $keptIds[] = $model->id;
                }

                if (!empty($keptIds)) {
                    FarmExperience::whereNotIn('id', $keptIds)->delete();
                }
            });

            return response()->json([
                'message' => 'Experiences saved successfully',
                'success' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving farm experiences: ' . $e->getMessage());
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
