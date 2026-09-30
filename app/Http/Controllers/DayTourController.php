<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\DayTour;
use App\Models\DayTourSwiperImage;
use App\Models\DayTourText;
use App\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DayTourController extends Controller
{
    public function index()
    {
        if (request()->is('admin/*')) {
            $tours = DayTour::with('activities', 'swiperImages')->get();
            $packages = $tours; // for compatibility
            $activities = Activity::all();
            $texts = DayTourText::all();
            $images = DayTourSwiperImage::whereNotNull('daytour_id')->ordered()->get() ?? collect();
            $infoTitle = DayTourText::where('title', 'info_title')->first();
            $infoSubtitle = DayTourText::where('title', 'info_subtitle')->first();
            $infoItemsJson = DayTourText::where('title', 'info_items')->first();
            $infoItems = $infoItemsJson ? json_decode($infoItemsJson->content, true) : [];
            return view('admin.dayTour.days', compact('tours', 'packages', 'activities', 'texts', 'images', 'infoTitle', 'infoSubtitle', 'infoItems', 'infoItemsJson'));
        } else {
            $swiperImages = DayTourSwiperImage::whereNull('daytour_id')->ordered()->get();
            $texts = DayTourText::whereNotNull('title')->pluck('content', 'title')->mapWithKeys(function ($content, $title) {
                return [strtolower($title) => $content];
            })->toArray();

            // Ensure info_items is always set with default if not exists
            if (!isset($texts['info_items'])) {
                $texts['info_items'] = json_encode([
                    ['icon' => 'bi-clock', 'text' => 'Operating hours: 8:00 AM - 5:00 PM daily'],
                    ['icon' => 'bi-calendar-check', 'text' => 'Advance booking recommended for guaranteed slots'],
                    ['icon' => 'bi-person', 'text' => 'Children under 3 years old enter for free'],
                    ['icon' => 'bi-people', 'text' => 'Group discounts available for 10+ persons'],
                    ['icon' => 'bi-person', 'text' => 'Wear comfy clothing and footwear for outdoor activities'],
                    ['icon' => 'bi-cup-straw', 'text' => 'Food and beverages available for purchase on site'],
                ]);
            }

            $packages = DayTour::with('activities', 'swiperImages')->get();

            // Fetch info section data from DayTourText
            $infoTitle = DayTourText::where('title', 'info_title')->first()->content ?? 'Important Information';
            $infoSubtitle = DayTourText::where('title', 'info_subtitle')->first()->content ?? 'Everything you need to know before your visit';
            $infoItemsJson = DayTourText::where('title', 'info_items')->first();
            if ($infoItemsJson && !empty($infoItemsJson->content) && $infoItemsJson->content !== '[]') {
                $decoded = json_decode($infoItemsJson->content, true);
                $infoItems = is_array($decoded) && !empty($decoded) ? $decoded : [
                    ['icon' => 'bi-clock', 'text' => 'Operating hours: 8:00 AM - 5:00 PM daily'],
                    ['icon' => 'bi-calendar-check', 'text' => 'Advance booking recommended for guaranteed slots'],
                    ['icon' => 'bi-person', 'text' => 'Children under 3 years old enter for free'],
                    ['icon' => 'bi-people', 'text' => 'Group discounts available for 10+ persons'],
                    ['icon' => 'bi-person', 'text' => 'Wear comfy clothing and footwear for outdoor activities'],
                    ['icon' => 'bi-cup-straw', 'text' => 'Food and beverages available for purchase on site'],
                ];
            } else {
                $infoItems = [
                    ['icon' => 'bi-clock', 'text' => 'Operating hours: 8:00 AM - 5:00 PM daily'],
                    ['icon' => 'bi-calendar-check', 'text' => 'Advance booking recommended for guaranteed slots'],
                    ['icon' => 'bi-person', 'text' => 'Children under 3 years old enter for free'],
                    ['icon' => 'bi-people', 'text' => 'Group discounts available for 10+ persons'],
                    ['icon' => 'bi-person', 'text' => 'Wear comfy clothing and footwear for outdoor activities'],
                    ['icon' => 'bi-cup-straw', 'text' => 'Food and beverages available for purchase on site'],
                ];
            }

            return view('daytour', compact('packages', 'swiperImages', 'texts', 'infoTitle', 'infoSubtitle', 'infoItems'));

            return view('daytour', compact('packages', 'swiperImages', 'texts', 'infoTitle', 'infoSubtitle', 'infoItems'));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:day_tours,name',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'max_guests' => 'required|integer|min:1',
            'pricing_type' => 'required|in:total,per_person',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
        ]);

        try {
            $tour = DayTour::create($request->only(['name', 'description', 'price', 'max_guests', 'pricing_type', 'is_active']));
            $tour->activities()->sync($request->activity_ids ?? []);

            // Handle package image upload (for card display only, separate from swiper images)
            if ($request->hasFile('image')) {
                try {
                    $file = $request->file('image');
                    $filePath = $file->getRealPath();
                    
                    // Upload to Cloudinary with transformations
                    $uploadedFile = Cloudinary::uploadApi()->upload($filePath, [
                        'folder' => config('cloudinary.upload.folder') ?: 'daytour-images',
                        'public_id' => 'daytour-' . $tour->id . '-' . time(),
                        // Upload a larger master so the site can display crisp images on wide/retina screens
                        'width' => 1600,
                        'height' => 900,
                        'crop' => 'fill',
                        'quality' => 'auto'
                    ]);

                    // Store the image URL in the day_tours table
                    $tour->image = $uploadedFile['secure_url'];
                    $tour->save();
                } catch (\Exception $e) {
                    Log::error('Image upload failed for DayTour: ' . $e->getMessage());
                    // Don't fail the tour creation if image upload fails
                }
            }

            // If the request is AJAX, return JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Day Tour created!',
                    'tour' => $tour->load('activities', 'swiperImages')
                ]);
            }

            // fallback for normal form submit
            return back()->with('success', 'Day Tour created!');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Failed to create Day Tour: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to create Day Tour: ' . $e->getMessage());
        }
    }

    public function update(Request $request, DayTour $dayTour)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:day_tours,name,' . $dayTour->id,
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'max_guests' => 'required|integer|min:1',
            'pricing_type' => 'required|in:total,per_person',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
        ]);

        try {
            $dayTour->update($request->only(['name', 'description', 'price', 'max_guests', 'pricing_type', 'is_active']));

            $dayTour->activities()->sync($request->activity_ids ?? []);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Upload new image to Cloudinary
                try {
                    $file = $request->file('image');
                    $filePath = $file->getRealPath();
                    
                    $uploadedFile = Cloudinary::uploadApi()->upload($filePath, [
                        'folder' => config('cloudinary.upload.folder') ?: 'daytour-images',
                        'public_id' => 'daytour-' . $dayTour->id . '-' . time(),
                        // Use a larger size for better clarity when images are displayed large
                        'width' => 1600,
                        'height' => 900,
                        'crop' => 'fill',
                        'quality' => 'auto'
                    ]);

                    // Store the image URL in the day_tours table
                    $dayTour->image = $uploadedFile['secure_url'];
                    $dayTour->save();
                } catch (\Exception $e) {
                    Log::error('Image upload failed for DayTour update: ' . $e->getMessage());
                }
            }

            // If the request is AJAX, return JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Day Tour updated!',
                    'tour' => $dayTour->load('activities', 'swiperImages')
                ]);
            }

            return back()->with('success', 'Day Tour updated!');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Failed to update Day Tour: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to update Day Tour: ' . $e->getMessage());
        }
    }

    public function destroy(DayTour $dayTour)
    {
        // Delete associated images from Cloudinary
        foreach ($dayTour->swiperImages as $img) {
            try {
                if ($img->public_id) {
                    Cloudinary::uploadApi()->destroy($img->public_id);
                }
            } catch (\Exception $e) {
                Log::error('Failed to delete image from Cloudinary: ' . $e->getMessage());
            }
        }

        $dayTour->delete();
        return back()->with('success', 'Day Tour deleted!');
    }
}
