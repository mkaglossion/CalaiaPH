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
use App\Models\StaycationPageText;
use App\Models\DoText;
use App\Models\FoodText;
use App\Models\SpecialText;
use App\Models\FarmText;
use App\Models\ShopText;

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
use App\Models\StaycationText;
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
        // Build availability from staycation inventory using one grouped query instead of
        // querying each date individually. This keeps the home page from doing 100+ DB hits.
        $availability = [];
        $start = Carbon::today();
        $end = (clone $start)->addMonths(6);
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();

        $staycationInventoryByDate = collect();
        if (Schema::hasTable('staycation_inventories')) {
            $staycationInventoryByDate = StaycationInventory::whereBetween('date', [$startDate, $endDate])
                ->select('date')
                ->selectRaw('COALESCE(SUM(available_quantity), 0) as total_available')
                ->groupBy('date')
                ->pluck('total_available', 'date');
        }

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');

            if ($d->lt(Carbon::today())) {
                $availability[$key] = [
                    'is_available' => false,
                    'min_quantity' => 0,
                ];
                continue;
            }

            $totalAvailable = (int) $staycationInventoryByDate->get($key, 0);

            $availability[$key] = [
                'is_available' => $totalAvailable > 0,
                'min_quantity' => $totalAvailable,
            ];
        }

        // Total quantity is no longer tracked at staycation level - use 0
        $totalQuantity = 0;

        // Build day tour availability data with a single grouped query.
        $dayTourAvailability = [];
        $dayTours = DayTour::where('is_active', true)->get();
        $totalDayTourGroups = $dayTours->sum('max_groups_per_day');

        $dayTourInventoryByDate = collect();
        if (Schema::hasTable('day_tour_inventories')) {
            $dayTourInventoryByDate = DayTourInventory::whereBetween('date', [$startDate, $endDate])
                ->where('is_blocked', false)
                ->select('date')
                ->selectRaw('COALESCE(SUM(available_groups), 0) as total_available_groups')
                ->groupBy('date')
                ->pluck('total_available_groups', 'date');
        }

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');

            if ($d->lt(Carbon::today())) {
                $dayTourAvailability[$key] = [
                    'is_available' => false,
                    'min_quantity' => 0,
                ];
                continue;
            }

            $totalAvailableGroups = (int) $dayTourInventoryByDate->get($key, 0);

            // If no inventory records exist for this date, fall back to the configured maximum.
            if ($totalAvailableGroups === 0) {
                $totalAvailableGroups = $totalDayTourGroups;
            }

            $dayTourAvailability[$key] = [
                'is_available' => $totalAvailableGroups > 0,
                'min_quantity' => $totalAvailableGroups,
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
        $pageCopyTexts = StaycationPageText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($content, $field) {
            return [strtolower($field) => $content];
        })->toArray();

        $staycationPageLabel = $this->pageTextValue($pageCopyTexts, ['staycation_page_label'], 'Staycation Options');
        $staycationPageTitle = $this->pageTextValue($pageCopyTexts, ['staycation_page_title'], 'All Glamping & Camping');
        $staycationPageIntro = $this->pageTextValue($pageCopyTexts, ['staycation_page_intro'], 'Browse every room directly on the page. The category modals are still available below if you want the full feature list and gallery.');

        // Get all staycations from admin panel
        $staycations = Staycation::ordered()->with([
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
            $description = $this->resolveStaycationDescription($staycation);
            $image = $staycation->image ?: \App\Helpers\ImageHelper::placeholder();

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

            // Determine current season type and a display rate for today
            $seasonType = $this->determineSeasonType($today);
            $currentPrice = $this->getStaycationDisplayRate($staycation, \Carbon\Carbon::parse($today), $seasonType);

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

        $vrData = [
            'glamping' => $this->defaultVrData('glamping'),
            'camping' => $this->defaultVrData('camping'),
        ];

        foreach ($staycations as $staycation) {
            $roomType = strtolower(trim((string) ($roomDetails[$staycation->id]['type'] ?? $staycation->type ?? 'camping')));
            $vrData[(string) $staycation->id] = $this->buildVrDataForStaycation($staycation, $roomType);
        }

        $data = compact('swiperImages', 'texts', 'pageCopyTexts', 'staycationPageLabel', 'staycationPageTitle', 'staycationPageIntro', 'staycations', 'availability', 'roomDetails', 'checkin', 'checkout', 'vrData');
        $data['active'] = 'staycation';
        
        return view('staycation', $data);
    }

    private function resolveStaycationDescription(Staycation $staycation): string
    {
        $textRecord = $staycation->texts->first(function ($text) {
            $section = strtolower(trim((string) ($text->section ?? '')));
            return !in_array($section, ['features', 'vr'], true);
        });

        $description = preg_replace('/\s+/', ' ', trim((string) ($textRecord?->content ?? '')));

        return $description !== ''
            ? $description
            : 'Experience the beauty of nature with our premium accommodation options.';
    }

    private function buildVrDataForStaycation(Staycation $staycation, string $roomType): array
    {
        $fallback = $this->defaultVrData($roomType, $staycation->name ?? ucfirst($roomType));
        $vrTexts = $staycation->texts
            ->filter(fn ($text) => strtolower(trim((string) ($text->section ?? ''))) === 'vr')
            ->values();

        $latestText = $vrTexts->sortByDesc('id')->first();
        $decoded = $latestText ? $this->decodeVrContent($latestText->content) : [];
        $images = [];
        $seen = [];

        foreach ($vrTexts->sortBy('id') as $vrText) {
            $json = $this->decodeVrContent($vrText->content);

            $rawImages = is_array($json['images'] ?? null) ? $json['images'] : [];
            foreach ($rawImages as $image) {
                if (!is_array($image)) {
                    continue;
                }

                $src = trim((string) ($image['src'] ?? ''));
                if ($src === '') {
                    continue;
                }

                $key = trim((string) ($image['public_id'] ?? $src));
                if ($key !== '' && isset($seen[$key])) {
                    continue;
                }

                if ($key !== '') {
                    $seen[$key] = true;
                }

                $images[] = [
                    'src' => \App\Helpers\ImageHelper::src($src),
                    'title' => $this->sanitizeVrSlideLabel((string) ($image['title'] ?? '')),
                    'desc' => $this->sanitizeVrSlideDescription((string) ($image['desc'] ?? '')),
                    'width' => (int) ($image['width'] ?? 0),
                    'height' => (int) ($image['height'] ?? 0),
                    'isPanoramic' => (bool) ($image['isPanoramic'] ?? false),
                ];
            }
        }

        $badgeLabel = trim((string) ($decoded['badgeLabel'] ?? $decoded['badge_label'] ?? ''));
        if ($badgeLabel === '') {
            $badgeLabel = $roomType === 'glamping' ? 'Glamping' : 'Camping';
        }

        return [
            'roomName' => $staycation->name ?? $fallback['roomName'],
            'roomType' => $roomType,
            'title' => $this->sanitizeVrSlideLabel((string) ($decoded['title'] ?? '')),
            'description' => $this->sanitizeVrSlideDescription((string) ($decoded['description'] ?? '')),
            'badgeLabel' => $badgeLabel,
            'badgeStyle' => $decoded['badgeStyle'] ?? $fallback['badgeStyle'],
            'headerStyle' => $decoded['headerStyle'] ?? $fallback['headerStyle'],
            'headerIcon' => $decoded['headerIcon'] ?? $fallback['headerIcon'],
            'images' => $images,
        ];
    }

    private function decodeVrContent(?string $content): array
    {
        if (!is_string($content) || trim($content) === '') {
            return [];
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function sanitizeVrSlideLabel(string $value): string
    {
        $label = trim($value);
        if ($label === '' || $this->looksLikeVrUploadSlug($label) || preg_match('/^slide\s+\d+$/i', $label)) {
            return '';
        }

        return $label;
    }

    private function sanitizeVrSlideDescription(string $value): string
    {
        $desc = trim($value);
        if ($desc === '' || $this->looksLikeVrUploadSlug($desc) || preg_match('/^slide\s+\d+$/i', $desc)) {
            return '';
        }

        return $desc;
    }

    private function looksLikeVrUploadSlug(string $value): bool
    {
        $text = trim($value);
        if ($text === '') {
            return true;
        }

        if (strlen($text) < 12) {
            return false;
        }

        if (preg_match('/\s/', $text)) {
            return false;
        }

        return (bool) preg_match('/^[a-z0-9_-]+$/i', $text);
    }

    private function defaultVrData(string $roomType, ?string $roomName = null): array
    {
        $roomType = $roomType === 'glamping' ? 'glamping' : 'camping';

        if ($roomType === 'glamping') {
            return [
                'roomName' => $roomName ?? 'Glamping',
                'title' => '',
                'description' => '',
                'badgeLabel' => 'Glamping',
                'badgeStyle' => 'background:rgba(167,94,62,0.10);color:#a75e3e',
                'headerStyle' => 'background:#a75e3e;color:white',
                'headerIcon' => 'bi bi-house-heart-fill',
                'images' => [],
            ];
        }

        return [
            'roomName' => $roomName ?? 'Camping',
            'title' => '',
            'description' => '',
            'badgeLabel' => 'Camping',
            'badgeStyle' => 'background:rgba(0,132,141,0.10);color:#00848d',
            'headerStyle' => 'background:#00848d;color:white',
            'headerIcon' => 'bi bi-tree-fill',
            'images' => [],
        ];
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

        $pricingService = app(\App\Services\PricingService::class);

        // For BYOT/Car Camping, calculate base price per night per unit (quantity)
        if ($isBYOT || $isCarCamping) {
            $quantity = $acc['extra_mattress'] ?? 1;

            $current = $checkin->copy();
            while ($current->lt($checkout)) {
                // Determine season type using centralized logic
                $seasonType = $this->determineSeasonType($current);
                $seasonKey = [
                    'Non-Peak' => 'nonPeak',
                    'Peak' => 'peak',
                    'Super-Peak' => 'superPeak',
                ][$seasonType] ?? 'nonPeak';

                $rate = $pricingService->getStaycationRate($staycation, $current, $seasonKey);

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
                $seasonKey = [
                    'Non-Peak' => 'nonPeak',
                    'Peak' => 'peak',
                    'Super-Peak' => 'superPeak',
                ][$seasonType] ?? 'nonPeak';

                $rate = $pricingService->getStaycationRate($staycation, $current, $seasonKey);

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

    /**
     * Resolve a room price for display on the frontend.
     *
     * This prefers the live season/date-based rate, then falls back to any
     * configured seasonal rate, then the room's base price, then the first
     * positive configured rate it can find.
     */
    private function getStaycationDisplayRate(Staycation $staycation, Carbon $date, string $seasonType): float
    {
        $pricingService = app(\App\Services\PricingService::class);
        $seasonKeyMap = [
            'Non-Peak' => 'nonPeak',
            'Peak' => 'peak',
            'Super-Peak' => 'superPeak',
        ];

        $rateKey = $seasonKeyMap[$seasonType] ?? 'nonPeak';
        $currentRate = (float) $pricingService->getStaycationRate($staycation, $date, $rateKey);
        if ($currentRate > 0) {
            return $currentRate;
        }

        $seasonRate = $this->getStaycationRateForSeason($staycation->price_configs ?? [], $seasonType);
        if ($seasonRate > 0) {
            return $seasonRate;
        }

        $basePrice = (float) ($staycation->price ?? 0);
        if ($basePrice > 0) {
            return $basePrice;
        }

        foreach ($staycation->price_configs ?? [] as $config) {
            $rates = $config['rates'] ?? [];
            foreach (['nonPeak', 'peak', 'superPeak'] as $seasonKey) {
                $rate = (float) ($rates[$seasonKey] ?? 0);
                if ($rate > 0) {
                    return $rate;
                }
            }
        }

        return 0;
    }

    public function activities()
    {
        // Get swiper images and texts for the hero slider
        $swiperImages = DoSwiperImage::all();
        $texts = DoText::orderByDesc('id')
            ->get()
            ->unique(function ($item) {
                return strtolower(trim((string) ($item->label ?? '')));
            })
            ->reverse()
            ->mapWithKeys(function ($item) {
                return [strtolower((string) ($item->label ?? '')) => $item->text];
            })
            ->toArray();

        $packagesSectionTitle = $this->pageTextValue($texts, [
            'packages_title',
            'heading',
            'title',
        ], 'Day Packages');

        $packagesSectionIntro = $this->pageTextValue($texts, [
            'packages_intro',
            'intro',
            'subtitle',
            'description',
        ], 'Start with our curated package options, then explore activities you can add to your stay.');

        // Temporarily merge packages into activities page (shown first).
        $packages = Package::with(['contents', 'images', 'inventory'])
            ->where('is_active', true)
            ->get();

        // Fetch activities from database with swiper images and group by category
        $activities = Activity::with('swiperImages')->get();
        $slidesByCategory = $this->formatActivitiesForSlides($activities, $activities);

        $data = compact('swiperImages', 'texts', 'slidesByCategory', 'packages', 'packagesSectionTitle', 'packagesSectionIntro');
        $data['active'] = 'activities';
        
        return view('activities', $data);
    }

    private function pageTextValue(array $texts, array $keys, string $fallback): string
    {
        foreach ($keys as $key) {
            $value = trim((string) ($texts[strtolower((string) $key)] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return $fallback;
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

        // Only get hero swiper images (food_id is null), not Learn More modal images
        $swiperImages = FoodSwiperImage::whereNull('food_id')->get();
        $texts = FoodText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($content, $field) {
            return [strtolower($field) => $content];
        })->toArray();

        $diningPageTitle = $this->pageTextValue($texts, ['dining_page_title'], 'Dining at CalaiaPH');
        $diningPageIntro = $this->pageTextValue($texts, ['dining_page_intro'], 'Experience farm-to-table dining in our elegant mess hall or enjoy cooking your own fresh ingredients straight from our farm. Perfect for celebrations, family gatherings, and intimate dinners.');
        $messHallTitle = $this->pageTextValue($texts, ['dining_messhall_title'], 'Mess Hall Dining');
        $messHallIntro = $this->pageTextValue($texts, ['dining_messhall_intro'], 'Our spacious and elegant mess hall is perfect for group dining and special occasions. Let our chefs prepare delicious farm-to-table meals using the freshest ingredients from our farm.');
        $cookYourOwnTitle = $this->pageTextValue($texts, ['dining_cook_title'], 'Cook Your Own Experience');
        $cookYourOwnIntro = $this->pageTextValue($texts, ['dining_cook_intro'], 'Enjoy cooking your own fresh ingredients harvested directly from our farm. Choose premium, locally sourced ingredients and create your own meal with guidance from our team.');
        $ctaTitle = $this->pageTextValue($texts, ['dining_cta_title'], 'Ready to Dine With Us?');
        $ctaIntro = $this->pageTextValue($texts, ['dining_cta_intro'], 'Message us to reserve your dining experience. We can accommodate lunch, dinner, group events, and special arrangements.');
        $ctaButton = $this->pageTextValue($texts, ['dining_cta_button'], 'Make a Reservation on Messenger');
        $ctaNote = $this->pageTextValue($texts, ['dining_cta_note'], 'Available for lunch and dinner reservations. Message us for inquiries.');

        $data = compact('swiperImages', 'texts', 'messHallFoods', 'cookYourOwnFoods', 'diningCategoryImages', 'diningPageTitle', 'diningPageIntro', 'messHallTitle', 'messHallIntro', 'cookYourOwnTitle', 'cookYourOwnIntro', 'ctaTitle', 'ctaIntro', 'ctaButton', 'ctaNote');
        $data['active'] = 'dining';
        
        return view('dining', $data);
    }

    public function groups()
    {
        return $this->renderPage(SpecialSwiperImage::class, SpecialText::class, 'groups', 'groups');
    }

    public function specialEvents()
    {
        $swiperImages = SpecialSwiperImage::all();
        $texts = SpecialText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($content, $field) {
            return [strtolower($field) => $content];
        })->toArray();
        $events = Event::with(['swiperImages', 'texts'])->get();

        $specialEventsPageTitle = $this->pageTextValue($texts, ['special_events_title', 'title', 'heading'], 'Our Special Events');
        $specialEventsPageIntro = $this->pageTextValue($texts, ['special_events_intro', 'intro', 'subtitle', 'description'], 'From intimate weddings to grand corporate celebrations, our beautiful farm setting provides the perfect backdrop for your special occasion.');
        $specialEventsCtaTitle = $this->pageTextValue($texts, ['special_events_cta_title', 'cta_title'], 'Have a Custom Event in Mind?');
        $specialEventsCtaIntro = $this->pageTextValue($texts, ['special_events_cta_intro', 'cta_intro'], 'We specialize in creating bespoke experiences tailored to your unique vision. From intimate gatherings to grand celebrations, let us bring your dream event to life.');
        $specialEventsCtaPrimaryLabel = $this->pageTextValue($texts, ['special_events_cta_primary_label', 'cta_primary_label'], 'Plan Custom Event');
        $specialEventsCtaPrimaryUrl = $this->pageTextValue($texts, ['special_events_cta_primary_url', 'cta_primary_url'], 'https://m.me/410774518796277');
        $specialEventsCtaSecondaryLabel = $this->pageTextValue($texts, ['special_events_cta_secondary_label', 'cta_secondary_label'], 'Email Us Now');
        $specialEventsCtaSecondaryUrl = $this->pageTextValue($texts, ['special_events_cta_secondary_url', 'cta_secondary_url'], 'https://mail.google.com/mail/?view=cm&fs=1&to=calaia.ph@gmail.com');

        $data = compact(
            'swiperImages',
            'texts',
            'events',
            'specialEventsPageTitle',
            'specialEventsPageIntro',
            'specialEventsCtaTitle',
            'specialEventsCtaIntro',
            'specialEventsCtaPrimaryLabel',
            'specialEventsCtaPrimaryUrl',
            'specialEventsCtaSecondaryLabel',
            'specialEventsCtaSecondaryUrl'
        );
        $data['active'] = 'special-events';

        return view('special-events', $data);
    }

    public function farm()
    {
        $swiperImages = FarmSwiperImage::all();
        $texts = FarmText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($content, $field) {
            return [strtolower($field) => $content];
        })->toArray();

        $farmPageTitle = $this->pageTextValue($texts, ['farm_page_title', 'title', 'heading'], 'Our Sustainable Farming');
        $farmPageIntro = $this->pageTextValue($texts, ['farm_page_intro', 'intro', 'subtitle', 'description'], 'Discover how we raise livestock and grow produce with respect for nature and animal welfare');

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
        
        $data = compact('swiperImages', 'texts', 'farmExperiences', 'farmPageTitle', 'farmPageIntro');
        $data['active'] = 'farm';

        return view('farm', $data);
    }

    public function ourStory()
    {
        return view('pages.our-story');
    }

    public function shop()
    {
        $swiperImages = \App\Models\ShopSwiperImage::all();
        $texts = ShopText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($content, $field) {
            return [strtolower($field) => $content];
        })->toArray();

        $shopPageTitle = $this->pageTextValue($texts, ['shop_page_title', 'title', 'heading'], 'Shop Our Products');
        $shopPageIntro = $this->pageTextValue($texts, ['shop_page_intro', 'intro', 'subtitle', 'description'], 'Farm-fresh produce and quality frozen goods delivered straight from our fields to your table');

        // Fetch active products by category
        $allProducts = \App\Models\Product::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        $frozenProducts = $allProducts->where('category', 'frozen');
        $freshProducts = $allProducts->where('category', 'fresh');

        $data = compact('swiperImages', 'texts', 'frozenProducts', 'freshProducts', 'shopPageTitle', 'shopPageIntro');
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
