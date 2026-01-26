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
use App\Models\Holiday;
use App\Models\DayTypeDay;
use App\Models\BookingSetting;

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

            // Compute total availability by summing per staycation:
            // - if an inventory record exists for the date, use its available_quantity
            // - otherwise, fall back to the staycation's base quantity
            $staycations = Staycation::select('id', 'quantity')->get();
            $inventoryForDate = StaycationInventory::where('date', $key)
                ->select('staycation_id', 'available_quantity')
                ->get()
                ->keyBy('staycation_id');

            $totalAvailable = 0;
            foreach ($staycations as $s) {
                $inv = $inventoryForDate->get($s->id);
                $qty = $inv ? (int) $inv->available_quantity : (int) $s->quantity;
                $totalAvailable += max(0, $qty);
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
        $swiperImages = StaySwiperImage::all();
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
                $qty = isset($inventories[$dateStr]) ? (int) $inventories[$dateStr]->available_quantity : (int) $staycation->quantity;
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
            $baseQty = max(0, (int)$staycation->quantity);
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

            $name = strtolower($staycation->name);
            if (strpos($name, 'glamping') !== false) {
                $type = 'glamping';
            } elseif (strpos($name, 'tent') !== false || strpos($name, 'camping') !== false) {
                $type = 'camping';
            } else {
                $type = 'camping'; // default
            }

            // Calculate the proper total price for the entire stay
            $mockAcc = ['extra_mattress' => 1]; // Default quantity for display
            $totalPrice = $this->calculateStaycationRoomPrice($staycation, $checkin, $checkout, $mockAcc);

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
                'price_configs' => $staycation->price_configs ?? []
            ];
        }

        return view('staycation', compact('swiperImages', 'texts', 'staycations', 'availability', 'roomDetails', 'checkin', 'checkout') + ['active' => 'staycation']);
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
                $dateStr = $current->format('Y-m-d');
                $dayOfWeek = (int)$current->dayOfWeek;

                // Determine day type: Holiday > Weekend > Weekday
                $dayType = 'nonPeak'; // default
                if (in_array($dateStr, $holidayDates)) {
                    $dayType = 'superPeak';
                } elseif (in_array($dayOfWeek, $weekendDayIds)) {
                    $dayType = 'peak';
                }

                // Find applicable price config for this date
                $rate = $this->getStaycationRateForDate($staycation->price_configs, $current, $dayType);

                if ($rate > 0) {
                    // For BYOT/Car Camping: multiply rate by quantity
                    $total += $rate * $quantity;
                }

                $current->addDay();
            }

            // No mattress or pet fees for BYOT/Car Camping
            $accommodationTotal = $total;
        } else {
            // Regular rooms: calculate nightly rates and add additional charges
            $current = $checkin->copy();
            while ($current->lt($checkout)) {
                $dateStr = $current->format('Y-m-d');
                $dayOfWeek = (int)$current->dayOfWeek;

                // Determine day type: Holiday > Weekend > Weekday
                $dayType = 'nonPeak'; // default
                if (in_array($dateStr, $holidayDates)) {
                    $dayType = 'superPeak';
                } elseif (in_array($dayOfWeek, $weekendDayIds)) {
                    $dayType = 'peak';
                }

                // Find applicable price config for this date
                $rate = $this->getStaycationRateForDate($staycation->price_configs, $current, $dayType);

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