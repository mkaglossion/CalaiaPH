<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
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

use App\Models\Food;
use App\Models\Menu;
use App\Models\MenuText;
use App\Models\Event;
use App\Models\DayTour;
use App\Models\Package;
use App\Models\PackageSwiperImage;
use App\Models\Product;
use App\Models\Activity;
use App\Models\Staycation;
use App\Models\StaycationInventory;
use App\Models\DayTourInventory;
use App\Models\Holiday;
use App\Models\DayTypeDay;
use App\Models\BookingSetting;
use App\Models\DiningCategoryImage;

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

            // Check if staycation_inventories table exists
            $totalAvailable = 0;
            if (Schema::hasTable('staycation_inventories')) {
                // Compute total availability by summing per staycation:
                // - if an inventory record exists for the date, use its available_quantity
                // - otherwise, fall back to 0 (no availability without inventory record)
                $staycations = Staycation::select('id')->get();
                $inventoryForDate = StaycationInventory::where('date', $key)
                    ->select('staycation_id', 'available_quantity')
                    ->get()
                    ->keyBy('staycation_id');

                foreach ($staycations as $s) {
                    $inv = $inventoryForDate->get($s->id);
                    $qty = $inv ? (int) $inv->available_quantity : 0;
                    $totalAvailable += max(0, $qty);
                }
            }

            $availability[$key] = [
                'is_available' => $totalAvailable > 0,
                'min_quantity' => $totalAvailable
            ];
        }

        // Total quantity is no longer tracked at staycation level - use 0
        $totalQuantity = 0;

        // Build day tour availability data
        $dayTourAvailability = [];
        $dayTours = DayTour::where('is_active', true)->get();
        $totalDayTourGroups = $dayTours->sum('max_groups_per_day');

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');

            // Past dates invalid
            if ($d->lt(Carbon::today())) {
                $dayTourAvailability[$key] = [
                    'is_available' => false,
                    'min_quantity' => 0
                ];
                continue;
            }

            // Get total available groups for this date from day tour inventory
            $totalAvailableGroups = DayTourInventory::where('date', $key)
                ->where('is_blocked', false)
                ->sum('available_groups');

            // If no inventory records exist for this date, fall back to sum of max_groups_per_day
            if ($totalAvailableGroups == 0) {
                $totalAvailableGroups = $totalDayTourGroups;
            }

            $dayTourAvailability[$key] = [
                'is_available' => $totalAvailableGroups > 0,
                'min_quantity' => $totalAvailableGroups
            ];
        }

        // Get total day tour groups for legend
        $totalDayTourQuantity = $totalDayTourGroups;

        return $this->renderPage(HomeSwiperImage::class, HomeText::class, 'home', 'home', 'label', 'text', compact('availability', 'totalQuantity', 'dayTourAvailability', 'totalDayTourQuantity'));
    }

    public function daytour()
    {
        $packages = DayTour::with(['swiperImages', 'texts', 'activities'])->get();
        $swiperImages = DayTourSwiperImage::whereNull('daytour_id')->get();
        $texts = DayTourText::whereNotNull('title')->pluck('content', 'title')->mapWithKeys(function ($content, $title) {
            return [strtolower($title) => $content];
        })->toArray();
        
        $data = compact('swiperImages', 'texts', 'packages');
        $data['active'] = 'daytour';
        
        return view('daytour', $data);
    }

    public function staycation()
    {

        // Use StaySwiperImage for admin-managed carousel (to match /admin/stay/where-to-stay)
        $swiperImages = \App\Models\StaySwiperImage::all();
        $texts = StayText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($content, $field) {
            return [strtolower($field) => $content];
        })->toArray();

        // Get all staycations from admin panel
        $staycations = Staycation::with([
            'texts',
            'swiperImages'
        ])->get();

        // Use default dates for availability display (next 2 days)
        $checkin = now()->addDay()->format('Y-m-d');
        $checkout = now()->addDays(3)->format('Y-m-d');

        // Calculate availability and details for each staycation
        $availability = [];
        $roomDetails = [];

        $checkinCarbon = \Carbon\Carbon::parse($checkin);
        $checkoutCarbon = \Carbon\Carbon::parse($checkout);

        $today = now();
        // Map season type to rate key
        $seasonKeyMap = [
            'Non-Peak' => 'nonPeak',
            'Peak' => 'peak',
            'Super-Peak' => 'superPeak'
        ];

        foreach ($staycations as $staycation) {
            $totalNights = $checkinCarbon->diffInDays($checkoutCarbon);

            // Fetch inventory for this staycation in the selected range
            $inventories = StaycationInventory::where('staycation_id', $staycation->id)
                ->whereBetween('date', [$checkinCarbon->format('Y-m-d'), $checkoutCarbon->copy()->subDay()->format('Y-m-d')])
                ->get(['date', 'available_quantity'])
                ->keyBy(function ($i) { return \Carbon\Carbon::parse($i->date)->format('Y-m-d'); });

            $inventoryDetails = [];
            $current = $checkinCarbon->copy();
            $minOverRange = null;
            $availableNights = 0;

            while ($current->lt($checkoutCarbon)) {
                $dateStr = $current->format('Y-m-d');
                // Get from inventory table, if not exists use base quantity
                $qty = isset($inventories[$dateStr]) ? (int) $inventories[$dateStr]->available_quantity : (int) $staycation->base_quantity;
                $inventoryDetails[$dateStr] = [
                    'min_quantity' => $qty,
                    'is_available' => $qty > 0,
                ];
                $minOverRange = is_null($minOverRange) ? $qty : min($minOverRange, $qty);
                if ($qty > 0) $availableNights++;
                $current->addDay();
            }

            $minOverRange = $minOverRange ?? 0;
            $isAvailable = $minOverRange > 0;

            // Dynamic status based on threshold (30% of base quantity)
            $baseQty = max(0, (int)$staycation->base_quantity);
            $threshold = max(1, (int) floor($baseQty * 0.3));
            $status = 'unavailable';
            if ($minOverRange === 0) {
                $status = 'unavailable';
            } elseif ($minOverRange <= $threshold) {
                $status = 'limited';
            } else {
                $status = 'available';
            }

            $availability[$staycation->id] = [
                'available' => $isAvailable,
                'partially_available' => $isAvailable && $availableNights < $totalNights,
                'available_nights' => $availableNights,
                'total_nights' => $totalNights,
                'inventory_details' => $inventoryDetails,
                'total_quantity' => $baseQty,
                'min_quantity' => $minOverRange,
                'has_inventory' => true
            ];

            // Prepare room details for display
            $description = $staycation->texts->first()->description ?? 'Experience the beauty of nature with our premium accommodation options.';
            $image = $staycation->image ?? asset('assets/images/carousel/Glamping-1.jpg');

            $name = strtolower((string) $staycation->name);
            $storedType = strtolower((string) ($staycation->type ?? ''));
            if (in_array($storedType, ['glamping', 'camping', 'byot', 'car camping'], true)) {
                $type = $storedType;
            } elseif (strpos($name, 'byot') !== false || strpos($name, 'bring your own') !== false || strpos($name, 'car camping') !== false) {
                $type = 'camping';
            } elseif (strpos($name, 'glamping') !== false || strpos($name, 'dome') !== false || strpos($name, 'bell') !== false) {
                $type = 'glamping';
            } elseif (strpos($name, 'tent') !== false || strpos($name, 'camping') !== false) {
                $type = 'camping';
            } else {
                $type = 'camping'; // default
            }

            // Calculate the proper total price for the entire stay
            $mockAcc = ['extra_mattress' => 1]; // Default quantity for display
            $totalPrice = $this->calculateStaycationRoomPrice($staycation, $checkin, $checkout, $mockAcc);

            // Determine current season type and price for today
            $seasonType = $this->determineSeasonType($today);
            $rateKey = $seasonKeyMap[$seasonType] ?? 'nonPeak';
            $config = $staycation->price_configs[0] ?? [];
            $rates = $config['rates'] ?? [];
            $currentPrice = $rates[$rateKey] ?? null;

            $roomDetails[$staycation->id] = [
                'id' => $staycation->id,
                'name' => $staycation->name,
                'type' => $type,
                'description' => $description,
                'image' => $image,
                'status' => $status,
                'available_quantity' => $minOverRange,
                'total_quantity' => $baseQty,
                'price' => $totalPrice, // Total price for the entire stay
                'price_configs' => $staycation->price_configs ?? [],
                'seasonType' => $seasonType,
                'currentPrice' => $currentPrice
            ];
        }

        $data = compact('swiperImages', 'texts', 'staycations', 'availability', 'roomDetails', 'checkin', 'checkout');
        $data['active'] = 'staycation';
        
        return view('staycation', $data);
    }

    private function calculateStaycationRoomPrice(Staycation $staycation, string $checkinDate, string $checkoutDate, array &$acc): float
    {
        $total = 0;
        $accommodationTotal = 0;
        $checkin = \Carbon\Carbon::parse($checkinDate);
        $checkout = \Carbon\Carbon::parse($checkoutDate);
        $nights = $checkin->diffInDays($checkout);

        // Detect if this is a BYOT or Car Camping room
        $roomName = strtolower($staycation->name);
        $isBYOT = strpos($roomName, 'byot') !== false || strpos($roomName, 'bring your own tent') !== false;
        $isCarCamping = strpos($roomName, 'car camping') !== false;

        // Get holiday dates
        $holidayDates = Holiday::whereBetween('date', [$checkinDate, $checkout->copy()->subDay()->format('Y-m-d')])
            ->pluck('date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Get weekend day IDs
        $weekendDayIds = DayTypeDay::whereHas('dayType', fn($q) => $q->where('name', 'Weekend'))
            ->pluck('weekday')
            ->toArray();

        // For BYOT/Car Camping, calculate base price per night per unit (quantity)
        if ($isBYOT || $isCarCamping) {
            $quantity = $acc['extra_mattress'] ?? 1;

            $current = $checkin->copy();
            while ($current->lt($checkout)) {
                // Determine season type using centralized logic
                $seasonType = $this->determineSeasonType($current);

                // Find applicable price config for this season
                $rate = $this->getStaycationRateForSeason($staycation->price_configs, $seasonType);

                if ($rate > 0) {
                    // For BYOT/Car Camping: multiply rate by quantity
                    $total += $rate * $quantity;
                }

                $current->addDay();
            }

            // No mattress or pet fees for BYOT/Car Camping
            $accommodationTotal = $total;
        } else {
            // Regular rooms: calculate nightly rates using season types
            $current = $checkin->copy();
            while ($current->lt($checkout)) {
                // Determine season type using centralized logic
                $seasonType = $this->determineSeasonType($current);

                // Find applicable price config for this season
                $rate = $this->getStaycationRateForSeason($staycation->price_configs, $seasonType);

                if ($rate > 0) {
                    $total += $rate;
                }

                $current->addDay();
            }

            // Store the base accommodation price (before extras)
            $accommodationTotal = $total;

            // Add extra mattress fee
            $extraMattresses = $acc['extra_mattress'] ?? 0;
            $total += $this->calculateMattressFee($extraMattresses, $nights);

            // Add pet fees
            if (!empty($acc['pet_agreement']) && !empty($acc['num_pets'])) {
                $total += $this->calculatePetFee($acc['num_pets']);
            }
        }

        // Store accommodation price in acc for later use
        $acc['accommodation_price'] = $accommodationTotal;

        return $total;
    }

    private function getStaycationRateForDate(array $priceConfigs, \Carbon\Carbon $date, string $dayType): float
    {
        $dateStr = $date->format('Y-m-d');
        $dayOfWeek = (int)$date->dayOfWeek;

        foreach ($priceConfigs as $config) {
            $applies = false;

            if (($config['type'] ?? 'day') === 'day') {
                // Check if day matches
                $configDays = $config['days'] ?? [];
                if (in_array($date->format('D'), $configDays)) {
                    $applies = true;
                }
            } elseif (($config['type'] ?? 'day') === 'date') {
                // Check specific dates
                $configDates = $config['dates'] ?? '';
                if (str_contains($configDates, $dateStr) || $configDates === $dateStr) {
                    $applies = true;
                }
            }

            if ($applies) {
                $rates = $config['rates'] ?? [];
                return (float)($rates[$dayType] ?? 0);
            }
        }

        // Fallback to first config's rate
        if (!empty($priceConfigs)) {
            $rates = $priceConfigs[0]['rates'] ?? [];
            return (float)($rates[$dayType] ?? 0);
        }

        return 0;
    }

    protected function calculateMattressFee($mattressCount, $nights)
    {
        $pricePerNight = BookingSetting::getValue('mattress_price_per_night', 500); // default 500
        return (int)$mattressCount * (int)$pricePerNight * max(1, $nights);
    }

    protected function calculatePetFee($petCount)
    {
        $petPrice = BookingSetting::getValue('pet_price', 2000); // default 2000
        return (int)$petCount * (int)$petPrice;
    }

    /**
     * Determine season type for a given date using centralized logic
     */
    private function determineSeasonType(\Carbon\Carbon $date): string
    {
        $dateStr = $date->format('Y-m-d');

        // Check for peak periods in priority order (Super-Peak > Peak > Non-Peak)
        $peakPeriods = \App\Models\PeakPeriod::with('peakType')
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->orderBy(\App\Models\PeakType::select('priority')->whereColumn('peak_types.id', 'peak_periods.peak_type_id'), 'desc')
            ->first();

        if ($peakPeriods) {
            return $peakPeriods->peakType->name;
        }

        return 'Non-Peak'; // Default season
    }

    /**
     * Get staycation rate for a specific season type
     */
    private function getStaycationRateForSeason(array $priceConfigs, string $seasonType): float
    {
        if (empty($priceConfigs)) {
            return 0;
        }

        // Map season type to rate key
        $seasonKeyMap = [
            'Non-Peak' => 'nonPeak',
            'Peak' => 'peak',
            'Super-Peak' => 'superPeak'
        ];

        $rateKey = $seasonKeyMap[$seasonType] ?? 'nonPeak';

        // Get the first price config (assuming single config per staycation)
        $config = $priceConfigs[0] ?? [];
        $rates = $config['rates'] ?? [];

        return (float)($rates[$rateKey] ?? 0);
    }

    public function activities()
    {
        // Get swiper images and texts for the hero slider
        $swiperImages = DoSwiperImage::all();
        $texts = DoText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($content, $field) {
            return [strtolower($field) => $content];
        })->toArray();

        // Temporarily merge packages into activities page (shown first).
        $packages = Package::with(['contents', 'images', 'inventory'])
            ->where('is_active', true)
            ->get();

        // Fetch activities from database with swiper images and group by category
        $activities = Activity::with('swiperImages')->get();
        $slidesByCategory = $this->formatActivitiesForSlides($activities, $activities);

        $data = compact('swiperImages', 'texts', 'slidesByCategory', 'packages');
        $data['active'] = 'activities';
        
        return view('activities', $data);
    }

    /**
     * Format activities data for carousel slides
     */
    private function formatActivitiesForSlides($activities, $originalActivities)
    {
        $activitiesByCategory = [
            'adventure' => [],
            'sports' => [],
            'rrcamp' => [],
            'farm' => [],
        ];

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
            // Check if image is already a full URL (from Cloudinary) or a local path
            $carouselImage = '';
            if ($activity->image) {
                if (filter_var($activity->image, FILTER_VALIDATE_URL)) {
                    $carouselImage = $activity->image; // Already a full URL
                } else {
                    $carouselImage = asset('storage/' . $activity->image); // Local storage path
                }
            } else {
                $carouselImage = $modalImages[0] ?? ''; // Fallback to first swiper image
            }

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
                    $carouselImg = '';
                    if ($activity && $activity->image) {
                        if (filter_var($activity->image, FILTER_VALIDATE_URL)) {
                            $carouselImg = $activity->image; // Already a full URL
                        } else {
                            $carouselImg = asset('storage/' . $activity->image); // Local storage path
                        }
                    } else {
                        $carouselImg = $left['images'][0] ?? ''; // Fallback to first swiper image
                    }

                    $slide['left'] = [
                        'id'      => $activity->id,
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
                    $carouselImg = '';
                    if ($activity && $activity->image) {
                        if (filter_var($activity->image, FILTER_VALIDATE_URL)) {
                            $carouselImg = $activity->image; // Already a full URL
                        } else {
                            $carouselImg = asset('storage/' . $activity->image); // Local storage path
                        }
                    } else {
                        $carouselImg = $right['images'][0] ?? ''; // Fallback to first swiper image
                    }

                    $slide['right'] = [
                        'id'      => $activity->id,
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
        $messHallFoods = Food::with('swiperImages')
            ->where('category', 'messhall')
            ->where('is_active', true)
            ->get();
        $cookYourOwnFoods = Food::with('swiperImages')
            ->where('category', 'cook_your_own')
            ->where('is_active', true)
            ->get();
        $diningCategoryImages = DiningCategoryImage::all()->keyBy('category');
        $diningDetails = [
            'Harvest Your Own – Pick and cook farm-fresh ingredients',
            'Cochinillo – Whole roast pig (pre-order required)',
            'Fresh farm-to-table ingredients available',
            'Guided cooking sessions with our chefs'
        ];
        
        // Only get hero swiper images (food_id is null), not Learn More modal images
        $swiperImages = FoodSwiperImage::whereNull('food_id')->get();
        $texts = FoodText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($content, $field) {
            return [strtolower($field) => $content];
        })->toArray();
        
        $data = compact('swiperImages', 'texts', 'messHallFoods', 'cookYourOwnFoods', 'diningCategoryImages', 'diningDetails');
        $data['active'] = 'dining';
        
        return view('dining', $data);
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
        // Get farm experiences from database
        $farmExperiences = \App\Models\FarmExperience::orderBy('sort_order')->get()->map(function($exp) {
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
            ];
        })->toArray();
        
        return $this->renderPage(FarmSwiperImage::class, FarmText::class, 'farm', 'farm', 'label', 'text', compact('farmExperiences'));
    }

    public function ourStory()
    {
        return view('pages.our-story');
    }

    public function shop()
    {
        $swiperImages = \App\Models\ShopSwiperImage::all();
        $texts = \App\Models\FarmText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($content, $field) {
            return [strtolower($field) => $content];
        })->toArray();

        // Fetch active products by category
        $allProducts = \App\Models\Product::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        $frozenProducts = $allProducts->where('category', 'frozen');
        $freshProducts = $allProducts->where('category', 'fresh');

        $data = compact('swiperImages', 'texts', 'frozenProducts', 'freshProducts');
        $data['active'] = 'shop';

        return view('shop', $data);
    }

    public function stay()
    {
        return $this->renderPage(StaySwiperImage::class, StayText::class, 'stay', 'stay');
    }

    public function food()
    {
        return $this->renderPage(FoodSwiperImage::class, FoodText::class, 'food', 'food');
    }

    public function sustainability()
    {
        return view('pages.sustainability');
    }

    public function ourServices()
    {
        return view('pages.our-services');
    }
    public function contact()
    {
        return view('contact', ['active' => 'contact']);
    }
    public function privacyNotice()
    {
        return view('pages.privacy-notice', ['active' => 'privacy-notice']);
    }
    public function calaiaPolicy()
    {
        return view('pages.calaia-policy', ['active' => 'calaia-policy']);
    }
    public function cookiesPolicy()
    {
        return view('pages.cookies-policy', ['active' => 'cookies-policy']);
    }
    public function termsService()
    {
        return view('pages.terms-service', ['active' => 'terms-service']);
    }

    public function packages()
    {
        $packages = Package::with(['contents', 'images', 'inventory'])
            ->where('is_active', true)
            ->get();
        
        $swiperImages = PackageSwiperImage::all();
        $texts = [];
        
        $data = compact('packages', 'swiperImages', 'texts');
        $data['active'] = 'packages';
        
        return view('packages', $data);
    }
}
