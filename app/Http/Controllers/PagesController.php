<?php

namespace App\Http\Controllers;

use App\Models\HomeSwiperImage;
use App\Models\DayTourSwiperImage;
use App\Models\DoSwiperImage;
use App\Models\FarmSwiperImage;
use App\Models\FoodSwiperImage;
use App\Models\SpecialSwiperImage;
use App\Models\StaySwiperImage;
use App\Models\HomeText;
use App\Models\DayTourText;
use App\Models\StayText;
use App\Models\DoText;
use App\Models\FoodText;
use App\Models\SpecialText;
use App\Models\FarmText;

use App\Models\Menu;
use App\Models\MenuText;
use App\Models\Event;
use App\Models\DayTour;
use App\Models\Product;
use App\Models\Activity;
use App\Models\Staycation;
use App\Models\StaycationInventory;

use Illuminate\Http\Request;
use Carbon\Carbon;

class PagesController extends Controller
{
    /**
     * Generic method to render pages with swiper images and texts
     */
    protected function renderPage($swiperModel, $textModel, $viewName, $activeName, $textField = 'label', $contentField = 'text', $extraData = [])
    {
        $swiperImages = $swiperModel::all();
        $texts = $textModel::whereNotNull($textField)->pluck($contentField, $textField)->mapWithKeys(function ($content, $field) {
            return [strtolower($field) => $content];
        })->toArray();

        $data = compact('swiperImages', 'texts') + ['active' => $activeName] + $extraData;

        return view($viewName, $data);
    }

    public function home()
    {
        // Build availability from staycation inventory
        $availability = [];
        $start = Carbon::today();
        $end = (clone $start)->addMonths(6);

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');

            // Past dates invalid
            if ($d->lt(Carbon::today())) {
                $availability[$key] = [
                    'is_available' => false,
                    'min_quantity' => 0
                ];
                continue;
            }

            // Get total available quantity for this date from inventory table
            $totalAvailable = StaycationInventory::where('date', $key)
                ->sum('available_quantity');

            // If no inventory records exist for this date, fall back to staycation quantity
            if ($totalAvailable == 0) {
                $totalAvailable = Staycation::sum('quantity');
            }

            $availability[$key] = [
                'is_available' => $totalAvailable > 0,
                'min_quantity' => $totalAvailable
            ];
        }

        // Get total quantity from staycations for legend
        $totalQuantity = Staycation::sum('quantity');

        return $this->renderPage(HomeSwiperImage::class, HomeText::class, 'home', 'home', 'label', 'text', compact('availability', 'totalQuantity'));
    }

    public function daytour()
    {
        $packages = DayTour::with(['swiperImages', 'texts', 'activities'])->get();
        return $this->renderPage(DayTourSwiperImage::class, DayTourText::class, 'daytour', 'daytour', 'label', 'text', compact('packages'));
    }

    public function staycation()
    {
        return $this->renderPage(StaySwiperImage::class, StayText::class, 'staycation', 'staycation');
    }

    public function activities()
    {
        // Get swiper images and texts for the hero slider
        $swiperImages = DoSwiperImage::all();
        $texts = DoText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($content, $field) {
            return [strtolower($field) => $content];
        })->toArray();

        // Fetch activities from database with swiper images and group by category
        $activities = Activity::with('swiperImages')->get();
        $slidesByCategory = $this->formatActivitiesForSlides($activities, $activities);

        return view('activities', compact('swiperImages', 'texts', 'slidesByCategory') + ['active' => 'activities']);
    }

    /**
     * Format activities data for carousel slides
     */
    private function formatActivitiesForSlides($activities, $originalActivities)
    {
        $activitiesByCategory = [];

        foreach ($activities as $activity) {
            $category = $activity->category;
            if (!isset($activitiesByCategory[$category])) {
                $activitiesByCategory[$category] = [];
            }

            // Collect swiper images for modal display
            $modalImages = [];
            foreach ($activity->swiperImages as $swiperImage) {
                $modalImages[] = $swiperImage->image_path; // Already a full Cloudinary URL
            }

            // Use main activity image for carousel display, fallback to first swiper image
            $carouselImage = $activity->image ? asset('storage/' . $activity->image) : ($modalImages[0] ?? '');

            $activitiesByCategory[$category][] = [
                'title' => $activity->name,
                'images' => $modalImages, // Only swiper images for modal
                'price' => $activity->price,
                'details' => array_filter([$activity->description, $activity->duration ? 'Duration: ' . $activity->duration : null]),
            ];
        }

        // Pair activities (2 per slide) for each category
        $slidesByCategory = [];
        foreach ($activitiesByCategory as $key => $activities) {
            $pairs = array_chunk($activities, 2);
            $slides = [];

            foreach ($pairs as $pair) {
                $left  = $pair[0] ?? null;
                $right = $pair[1] ?? null;

                $slide = [];
                if ($left) {
                    // Find the activity to get its main image for carousel
                    $activity = $originalActivities->firstWhere('name', $left['title']);
                    $carouselImg = $activity && $activity->image ? asset('storage/' . $activity->image) : ($left['images'][0] ?? '');

                    $slide['left'] = [
                        'id'      => $left['title'],
                        'title'   => $left['title'],
                        'img'     => $carouselImg, // Use activity image for carousel
                        'alt'     => $left['title'],
                        'price'   => $left['price'] ?? 0,
                        'details' => $left['details'] ?? [],
                        'images'  => $left['images'] ?? [], // Swiper images for modal
                    ];
                }
                if ($right) {
                    // Find the activity to get its main image for carousel
                    $activity = $originalActivities->firstWhere('name', $right['title']);
                    $carouselImg = $activity && $activity->image ? asset('storage/' . $activity->image) : ($right['images'][0] ?? '');

                    $slide['right'] = [
                        'id'      => $right['title'],
                        'title'   => $right['title'],
                        'img'     => $carouselImg, // Use activity image for carousel
                        'alt'     => $right['title'],
                        'price'   => $right['price'] ?? 0,
                        'details' => $right['details'] ?? [],
                        'images'  => $right['images'] ?? [], // Swiper images for modal
                    ];
                }

                $slides[] = $slide;
            }

            $slidesByCategory[$key] = $slides;
        }

        return $slidesByCategory;
    }

    public function dining()
    {
        $menus = Menu::with('swiperImages')->get();
        return $this->renderPage(FoodSwiperImage::class, FoodText::class, 'dining', 'dining', 'label', 'text', compact('menus'));
    }

    public function groups()
    {
        return $this->renderPage(SpecialSwiperImage::class, SpecialText::class, 'groups', 'groups');
    }

    public function specialEvents()
    {
        $events = Event::with(['swiperImages', 'texts'])->get();
        return $this->renderPage(SpecialSwiperImage::class, SpecialText::class, 'special-events', 'special-events', 'label', 'text', compact('events'));
    }

    public function farm()
    {
        return $this->renderPage(FarmSwiperImage::class, FarmText::class, 'farm', 'farm');
    }

    public function shop()
    {
        return $this->renderPage(FarmSwiperImage::class, FarmText::class, 'shop', 'shop');
    }

    public function stay()
    {
        return $this->renderPage(StaySwiperImage::class, StayText::class, 'stay', 'stay');
    }

    public function food()
    {
        return $this->renderPage(FoodSwiperImage::class, FoodText::class, 'food', 'food');
    }
}