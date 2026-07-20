<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FarmExperience;
use App\Services\CloudinaryImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
                'image_public_id' => $exp->image_public_id,
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
    public function save(Request $request, CloudinaryImageService $cloudinary)
    {
        try {
            $validated = $request->validate([
                'experiences' => ['present', 'array', 'max:30'],
                'experiences.*.id' => ['nullable', 'integer', 'min:1'],
                'experiences.*.title' => ['required', 'string', 'max:160'],
                'experiences.*.desc' => ['nullable', 'string', 'max:2000'],
                'experiences.*.time' => ['nullable', 'string', 'max:160'],
                'experiences.*.capacity' => ['nullable', 'string', 'max:160'],
                'experiences.*.icon' => ['nullable', 'string', 'max:100'],
                'experiences.*.cta' => ['nullable', 'string', 'max:160'],
                'experiences.*.badge' => ['nullable', 'string', 'max:100'],
                'experiences.*.img' => ['nullable', 'string', 'max:2048'],
                'experiences.*.image_public_id' => ['nullable', 'string', 'max:255'],
            ]);
            $experiences = $validated['experiences'];
            $oldPublicIds = FarmExperience::query()->pluck('image_public_id')->filter()->unique()->all();

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
                        'image_public_id' => $exp['image_public_id'] ?? null,
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
                } else {
                    FarmExperience::query()->delete();
                }
            });

            $currentPublicIds = FarmExperience::query()->pluck('image_public_id')->filter()->unique()->all();
            foreach (array_diff($oldPublicIds, $currentPublicIds) as $publicId) {
                try {
                    $cloudinary->destroy($publicId);
                } catch (\Throwable $exception) {
                    Log::warning('Unable to remove replaced farm experience image from Cloudinary.', [
                        'public_id' => $publicId,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'message' => 'Experiences saved successfully',
                'success' => true,
            ]);
        } catch (ValidationException $e) {
            throw $e;
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
    public function uploadImage(Request $request, CloudinaryImageService $cloudinary)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);

            if (!$request->hasFile('image')) {
                return response()->json(['message' => 'No image provided', 'success' => false], 400);
            }

            $uploaded = $cloudinary->upload($request->file('image'), 'farm/experiences');

            return response()->json([
                'success' => true,
                'imageUrl' => $uploaded['url'],
                'publicId' => $uploaded['public_id'],
                'message' => 'Image uploaded successfully'
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Cloudinary upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading image: ' . $e->getMessage()
            ], 500);
        }
    }
}
