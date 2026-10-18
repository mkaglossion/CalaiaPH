<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Http\Controllers\Controller;

/**
 * Generic Media Controller for Swiper Images
 * 
 * Replaces 14 identical swiper image controllers with configurable model handling.
 * Supports: Activity, DayTour, Do, Event, Farm, Food, Home, Menu, Package, Shop, Special, Staycation, Stay + FarmInfo
 * 
 * Usage in routes:
 *   GenericMediaController@index('activity_swiper_images', 'Activity')
 *   GenericMediaController@store('activity_swiper_images', 'Activity')
 * 
 * Supports both:
 *   - Direct model storage: where media belongs to main model (home_swiper_images)
 *   - Foreign key model: where media has parent_id (activity_swiper_images with activity_id)
 */
class GenericMediaController extends Controller
{
    /**
     * Configuration for each media type
     */
    private static $mediaConfigs = [
        'activity_swiper_images' => [
            'model' => 'ActivitySwiperImage',
            'parent_model' => 'Activity',
            'foreign_key' => 'activity_id',
            'view_path' => 'admin.whatToDo.activityImages',
            'has_parent' => true,
        ],
        'day_tour_swiper_images' => [
            'model' => 'DayTourSwiperImage',
            'parent_model' => 'DayTour',
            'foreign_key' => 'daytour_id',
            'view_path' => 'admin.dayTour.images',
            'has_parent' => true,
        ],
        'home_swiper_images' => [
            'model' => 'HomeSwiperImage',
            'view_path' => 'admin.home.images',
            'has_parent' => false,
        ],
        'farm_swiper_images' => [
            'model' => 'FarmSwiperImage',
            'view_path' => 'admin.farm.images',
            'has_parent' => false,
        ],
        'food_swiper_images' => [
            'model' => 'FoodSwiperImage',
            'view_path' => 'admin.food.images',
            'has_parent' => false,
        ],
        'menu_swiper_images' => [
            'model' => 'MenuSwiperImage',
            'parent_model' => 'Menu',
            'foreign_key' => 'menu_id',
            'view_path' => 'admin.menu.images',
            'has_parent' => true,
        ],
        'event_swiper_images' => [
            'model' => 'EventSwiperImage',
            'parent_model' => 'Event',
            'foreign_key' => 'event_id',
            'view_path' => 'admin.event.images',
            'has_parent' => true,
        ],
        'shop_swiper_images' => [
            'model' => 'ShopSwiperImage',
            'view_path' => 'admin.shop.images',
            'has_parent' => false,
        ],
        'package_swiper_images' => [
            'model' => 'PackageSwiperImage',
            'parent_model' => 'ActivityPackage',
            'foreign_key' => 'package_id',
            'view_path' => 'admin.packages.images',
            'has_parent' => true,
        ],
        'special_swiper_images' => [
            'model' => 'SpecialSwiperImage',
            'view_path' => 'admin.special.images',
            'has_parent' => false,
        ],
        'do_swiper_images' => [
            'model' => 'DoSwiperImage',
            'view_path' => 'admin.do.images',
            'has_parent' => false,
        ],
        'staycation_swiper_images' => [
            'model' => 'StaycationSwiperImage',
            'parent_model' => 'Staycation',
            'foreign_key' => 'staycation_id',
            'view_path' => 'admin.staycation.images',
            'has_parent' => true,
        ],
        'farm_info_swiper_images' => [
            'model' => 'FarmInfoSwiperImage',
            'parent_model' => 'FarmInfo',
            'foreign_key' => 'farm_info_id',
            'view_path' => 'admin.farmInfo.images',
            'has_parent' => true,
        ],
        'stay_swiper_images' => [
            'model' => 'StaySwiperImage',
            'view_path' => 'admin.stay.images',
            'has_parent' => false,
        ],
    ];

    /**
     * Get configuration for media type
     */
    private function getConfig(string $mediaType): array
    {
        if (!isset(self::$mediaConfigs[$mediaType])) {
            abort(404, "Media type '{$mediaType}' not configured");
        }
        return self::$mediaConfigs[$mediaType];
    }

    /**
     * Get model class from configuration
     */
    private function getModel(string $mediaType): object
    {
        $config = $this->getConfig($mediaType);
        $modelClass = 'App\\Models\\' . $config['model'];
        return new $modelClass();
    }

    /**
     * List all images for a media type
     */
    public function index(string $mediaType, ?int $parentId = null)
    {
        $config = $this->getConfig($mediaType);
        $query = $this->getModel($mediaType)->query();

        if ($config['has_parent'] && $parentId) {
            $query->where($config['foreign_key'], $parentId);
        }

        $images = $query->latest()->get();
        $viewPath = $config['view_path'];
        return view($viewPath, compact('images'));
    }

    /**
     * Store a new image
     */
    public function store(Request $request, string $mediaType, ?int $parentId = null)
    {
        $config = $this->getConfig($mediaType);

        $validated = $request->validate([
            'image' => 'required|file|mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv|max:102400',
            'title' => 'nullable|string|max:255',
        ]);

        try {
            // Detect resource type based on mime type
            $mimeType = $request->file('image')->getMimeType();
            $resourceType = strpos($mimeType, 'video') !== false ? 'video' : 'image';

            $uploadResult = Cloudinary::uploadApi()->upload($request->file('image')->getRealPath(), [
                'resource_type' => $resourceType,
                'folder' => $mediaType,
                'unique_filename' => true,
            ]);

            $data = [
                'title' => $validated['title'] ?? null,
                'image_path' => $uploadResult['secure_url'],
                'public_id' => $uploadResult['public_id'],
            ];

            if ($config['has_parent'] && $parentId) {
                $data[$config['foreign_key']] = $parentId;
            }

            $this->getModel($mediaType)->create($data);

            return response()->json([
                'success' => true,
                'message' => ucfirst($resourceType) . ' uploaded successfully',
                'image' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update image display order
     */
    public function update(Request $request, string $mediaType, int $imageId)
    {
        $image = $this->getModel($mediaType)->findOrFail($imageId);

        $validated = $request->validate([
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $image->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully',
        ]);
    }

    /**
     * Delete an image
     */
    public function destroy(string $mediaType, int $imageId)
    {
        $config = $this->getConfig($mediaType);
        $image = $this->getModel($mediaType)->findOrFail($imageId);

        try {
            if ($image->public_id) {
                Cloudinary::uploadApi()->destroy(
                    $image->public_id,
                    ['resource_type' => $image->resource_type ?? 'image']
                );
            }

            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deletion failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get images for a specific parent (alternative endpoint)
     */
    public function getImages(string $mediaType, int $parentId)
    {
        $config = $this->getConfig($mediaType);

        if (!$config['has_parent']) {
            return response()->json(['error' => 'This media type does not support parent filtering'], 400);
        }

        $images = $this->getModel($mediaType)
            ->where($config['foreign_key'], $parentId)
            ->latest()
            ->get();

        return response()->json(['images' => $images]);
    }
}
