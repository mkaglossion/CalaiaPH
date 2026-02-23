<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiningCategoryImage;
use App\Models\FoodSwiperImage;
use App\Models\Food;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class DiningCategoryImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $diningImages = DiningCategoryImage::all()->keyBy('category');
        $images = FoodSwiperImage::with('food')->latest()->paginate(10);
        $foods = Food::orderBy('name')->get();
        return view('admin.dining.category-images.index', compact('diningImages', 'images', 'foods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'category' => 'required|in:messhall,cook_your_own',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);

            // Handle image upload to Cloudinary
            if ($request->hasFile('image')) {
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

            // Check if image already exists for this category
            $existingImage = DiningCategoryImage::where('category', $request->category)->first();

            if ($existingImage) {
                // Delete old image from Cloudinary if it exists
                if ($existingImage->public_id) {
                    try {
                        Cloudinary::uploadApi()->destroy($existingImage->public_id);
                    } catch (\Exception $e) {
                        // Continue even if delete fails
                    }
                }
                // Update existing record
                $existingImage->update([
                    'image_path' => $imagePath,
                    'public_id' => $publicId,
                ]);

                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Dining category image updated successfully!'
                    ]);
                }

                return redirect()->route('admin.dining.category-images.index')
                                ->with('success', 'Dining category image updated successfully!');
            } else {
                // Create new record
                DiningCategoryImage::create([
                    'category' => $request->category,
                    'image_path' => $imagePath,
                    'public_id' => $publicId,
                ]);

                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Dining category image created successfully!'
                    ]);
                }

                return redirect()->route('admin.dining.category-images.index')
                                ->with('success', 'Dining category image created successfully!');
            }
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
