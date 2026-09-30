<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiningCategoryImage;
use App\Models\FoodSwiperImage;
use App\Models\FoodText;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Facades\Cloudinary;

class DiningCategoryImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $diningImages = DiningCategoryImage::all()->keyBy('category');
        $texts = FoodText::latest()->get();
        // Only show Learn More modal images (food_id is set), not hero swiper images
        // Order by created_at ascending (1, 2, 3 - oldest first)
        $images = FoodSwiperImage::with('food')->whereNotNull('food_id')->orderBy('created_at', 'asc')->paginate(10);
        $foods = Food::orderBy('name')->get();
        return view('admin.dining.category-images.index', compact('diningImages', 'images', 'foods', 'texts'));
    }

    /**
     * Save all dining copy fields in a single transaction.
     */
    public function saveCopy(Request $request)
    {
        $validated = $request->validate([
            'page_title' => 'nullable|string|max:255',
            'page_intro' => 'nullable|string',
            'messhall_title' => 'nullable|string|max:255',
            'messhall_intro' => 'nullable|string',
            'cook_title' => 'nullable|string|max:255',
            'cook_intro' => 'nullable|string',
            'cta_title' => 'nullable|string|max:255',
            'cta_intro' => 'nullable|string',
            'cta_button' => 'nullable|string|max:255',
            'cta_note' => 'nullable|string',
        ]);

        $copyMap = [
            'dining_page_title' => $validated['page_title'] ?? '',
            'dining_page_intro' => $validated['page_intro'] ?? '',
            'dining_messhall_title' => $validated['messhall_title'] ?? '',
            'dining_messhall_intro' => $validated['messhall_intro'] ?? '',
            'dining_cook_title' => $validated['cook_title'] ?? '',
            'dining_cook_intro' => $validated['cook_intro'] ?? '',
            'dining_cta_title' => $validated['cta_title'] ?? '',
            'dining_cta_intro' => $validated['cta_intro'] ?? '',
            'dining_cta_button' => $validated['cta_button'] ?? '',
            'dining_cta_note' => $validated['cta_note'] ?? '',
        ];

        DB::transaction(function () use ($copyMap) {
            foreach ($copyMap as $label => $text) {
                FoodText::updateOrCreate(
                    ['label' => $label],
                    ['text' => $text]
                );
            }
        });

        return response()->json([
            'message' => 'Dining page copy saved successfully.',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'category' => 'required|in:messhall,cook_your_own',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'messhall_title' => 'nullable|string|max:255',
                'messhall_intro' => 'nullable|string',
                'cook_title' => 'nullable|string|max:255',
                'cook_intro' => 'nullable|string',
            ]);

            $existingImage = DiningCategoryImage::where('category', $validated['category'])->first();
            $hasImage = $request->hasFile('image');
            $oldPublicId = $existingImage?->public_id;

            if (!$hasImage && !$existingImage) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Please select an image before uploading.'
                    ], 422);
                }

                return back()->withErrors(['error' => 'Please select an image before uploading.']);
            }

            $copyMap = [];
            if ($validated['category'] === 'messhall') {
                $copyMap = [
                    'dining_messhall_title' => $validated['messhall_title'] ?? '',
                    'dining_messhall_intro' => $validated['messhall_intro'] ?? '',
                ];
            } elseif ($validated['category'] === 'cook_your_own') {
                $copyMap = [
                    'dining_cook_title' => $validated['cook_title'] ?? '',
                    'dining_cook_intro' => $validated['cook_intro'] ?? '',
                ];
            }

            $imagePath = null;
            $publicId = null;

            // Handle image upload to Cloudinary
            if ($hasImage) {
                $file = $request->file('image');
                $filePath = $file->getRealPath();
                
                try {
                    $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
                    $imagePath = $uploadResult['secure_url'];
                    $publicId = $uploadResult['public_id'];
                } catch (\Exception $e) {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'message' => 'Failed to upload image to cloud storage: ' . $e->getMessage()
                        ], 422);
                    }
                    return back()->withErrors(['error' => 'Failed to upload image to cloud storage']);
                }
            }

            DB::transaction(function () use ($copyMap, $validated, $existingImage, $imagePath, $publicId, $hasImage) {
                foreach ($copyMap as $label => $text) {
                    FoodText::updateOrCreate(
                        ['label' => $label],
                        ['text' => $text]
                    );
                }

                if ($hasImage) {
                    if ($existingImage) {
                        $existingImage->update([
                            'image_path' => $imagePath,
                            'public_id' => $publicId,
                        ]);
                    } else {
                        DiningCategoryImage::create([
                            'category' => $validated['category'],
                            'image_path' => $imagePath,
                            'public_id' => $publicId,
                        ]);
                    }
                }
            });

            if ($hasImage && $oldPublicId) {
                try {
                    Cloudinary::uploadApi()->destroy($oldPublicId);
                } catch (\Exception $e) {
                    // Continue even if delete fails
                }
            }

            $message = $hasImage
                ? ($existingImage ? 'Dining category image updated successfully!' : 'Dining category image created successfully!')
                : 'Hero copy updated successfully!';

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $message
                ]);
            }

            return redirect()->route('admin.dining.category-images.index')
                            ->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DiningCategoryImage $diningCategoryImage)
    {
        try {
            // Delete from Cloudinary
            if ($diningCategoryImage->public_id) {
                try {
                    Cloudinary::uploadApi()->destroy($diningCategoryImage->public_id);
                } catch (\Exception $e) {
                    // Continue even if delete fails
                }
            }
            
            $diningCategoryImage->delete();
            
            return redirect()->route('admin.dining.category-images.index')
                            ->with('success', 'Dining category image deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
