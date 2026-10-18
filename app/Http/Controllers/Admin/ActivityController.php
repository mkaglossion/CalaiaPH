<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\ActivityPackage;
use App\Services\CloudinaryImageService;

class ActivityController extends Controller
{
    public function __construct(private readonly CloudinaryImageService $cloudinaryImages)
    {
    }

    private function uploadImage(?\Illuminate\Http\UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        return $this->cloudinaryImages->upload($file, 'activities')['url'];
    }

    public function index()
    {
        $activities = Activity::latest()->paginate(10);
        $allActivities = Activity::select(['id', 'name', 'description', 'price'])->where('is_active', true)->limit(200)->get();  // For the package modal
        $activityPackages = ActivityPackage::with('activities')->latest()->paginate(10);
        return view('admin.whatToDo.activities', compact('activities', 'allActivities', 'activityPackages'));
    }

    public function show(Activity $activity_list)
    {
        return response()->json($activity_list);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:activities,name',
            'description' => 'nullable|string',
            'duration' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        try {
            $imageUrl = $this->uploadImage($request->file('image'));
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Image upload failed: ' . $e->getMessage(),
                'errors' => ['image' => ['Failed to upload image.']],
            ], 422);
        }

        Activity::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'duration' => $request->duration ?? '',
            'price' => $request->price ?? 0,
            'availability' => $request->availability ?? 'Available',
            'image' => $imageUrl,
            'category' => $request->category ?? '',
            'is_active' => $request->is_active ?? 1,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json(['message' => 'Activity added successfully.']);

    }

    public function update(Request $request, Activity $activity_list)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:activities,name,' . $activity_list->id,
            'description' => 'nullable|string',
            'duration' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'duration' => $request->duration ?? '',
            'price' => $request->price ?? $activity_list->price,
            'availability' => $request->availability ?? $activity_list->availability,
            'category' => $request->category ?? $activity_list->category,
            'is_active' => $request->is_active ?? $activity_list->is_active,
            'sort_order' => $request->sort_order ?? $activity_list->sort_order,
        ];

        if ($request->hasFile('image')) {
            try {
                $data['image'] = $this->uploadImage($request->file('image'));
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => 'Image upload failed: ' . $e->getMessage(),
                    'errors' => ['image' => ['Failed to upload image.']],
                ], 422);
            }
        }

        $activity_list->update($data);

        return response()->json(['message' => 'Activity updated successfully.']);
    }

    public function destroy(Activity $activity_list)
    {
        // Cloudinary images are automatically deleted when referenced
        // No need to manually delete from storage
        $activity_list->delete();
        return redirect()->back()->with('success', 'Activity deleted successfully!');
    }
}
