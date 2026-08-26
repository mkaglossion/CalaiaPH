<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staycation;
use App\Models\StaycationText;
use App\Models\StaycationSwiperImage;
use App\Models\PeakPeriod;
use App\Services\CloudinaryImageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class StaycationController extends Controller
{
    public function __construct(private readonly CloudinaryImageService $cloudinaryImages)
    {
    }

    private function uploadImage(?\Illuminate\Http\UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        return $this->cloudinaryImages->upload($file, 'staycations')['url'];
    }

    public function index()
    {
        $staycations = Staycation::ordered()->get();
        
        // Get current season
        $today = now()->format('Y-m-d');
        $currentSeasonPeriod = PeakPeriod::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->with('peakType')
            ->orderBy(\App\Models\PeakType::select('priority')->whereColumn('peak_types.id', 'peak_periods.peak_type_id'), 'desc')
            ->first();

        $currentSeason = $currentSeasonPeriod ? $currentSeasonPeriod->peakType->name : 'Non-Peak';
        
        $seasonKeyMap = [
            'Non-Peak' => 'nonPeak',
            'Peak' => 'peak',
            'Super-Peak' => 'superPeak'
        ];
        $currentSeasonKey = $seasonKeyMap[$currentSeason] ?? 'nonPeak';

        $pricingService = app(\App\Services\PricingService::class);
        $todayCarbon = now();
        $staycations->transform(function ($staycation) use ($pricingService, $todayCarbon, $currentSeasonKey, $currentSeason) {
            $staycation->setAttribute(
                'current_rate_preview',
                $pricingService->getStaycationRate($staycation, $todayCarbon, $currentSeasonKey)
            );
            $staycation->setAttribute('current_season_preview', $currentSeason);

            return $staycation;
        });
        
        return view('admin.staycations', compact('staycations', 'currentSeason', 'currentSeasonKey'));
    }

    public function store(Request $request)
    {
        Log::info('Store request started');
        
        $request->validate([
            'name' => 'required|string|max:255|unique:staycations,name',
            'days' => 'required|integer|min:1',
            'adults' => 'required|integer|min:0',
            'children' => 'required|integer|min:0',
            'max_guest_capacity' => 'nullable|integer|min:1|max:30',
            'included_guest_capacity' => 'nullable|integer|min:1|max:30',
            'description' => 'nullable|string|max:1000',
            'priceConfigs' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $defaultIncludedCapacity = max(1, (int) $request->adults);
        $includedGuestCapacity = max(1, (int)($request->input('included_guest_capacity', $defaultIncludedCapacity)));
        $capacity = $includedGuestCapacity;
        $requestedMaxGuestCapacity = (int)($request->input('max_guest_capacity', 0));
        $maxGuestCapacity = max($includedGuestCapacity, $requestedMaxGuestCapacity);
        if ($maxGuestCapacity <= 0) {
            $maxGuestCapacity = null;
        }

        // Determine type based on name
        $name = strtolower($request->name);
        $type = 'glamping'; // default
        if (str_contains($name, 'byot') || str_contains($name, 'car camping') || str_contains($name, 'bring your own')) {
            $type = 'camping';
        }

        // Glamping rooms must always have at least 1 adult.
        if ($type === 'glamping' && (int) $request->adults < 1) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => [
                    'adults' => ['Glamping rooms must have at least 1 adult.']
                ]
            ], 422);
        }

        // Handle price configs
        $priceConfigs = [];
        if ($request->filled('priceConfigs')) {
            $decodedConfigs = is_string($request->priceConfigs)
                ? json_decode($request->priceConfigs, true)
                : $request->priceConfigs;

            if (!is_array($decodedConfigs)) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => [
                        'priceConfigs' => ['Pricing rules must be a valid JSON array.']
                    ]
                ], 422);
            }

            [$priceConfigs, $priceConfigErrors] = $this->normalizePriceConfigs($decodedConfigs);

            if (!empty($priceConfigErrors)) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $priceConfigErrors
                ], 422);
            }

            if (empty($priceConfigs)) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => [
                        'priceConfigs' => ['Please add at least one pricing rule.']
                    ]
                ], 422);
            }
        }

        // Handle image upload
        try {
            $imagePath = $this->uploadImage($request->file('image'));
        } catch (\Throwable $e) {
            Log::error('Image upload failed:', ['message' => $e->getMessage()]);
            return response()->json([
                'message' => 'Image upload failed: ' . $e->getMessage(),
                'errors' => ['image' => ['Failed to upload image to Cloudinary: ' . $e->getMessage()]]
            ], 422);
        }

        $supportsMaxGuestCapacity = Schema::hasColumn('staycations', 'max_guest_capacity');
        $supportsIncludedGuestCapacity = Schema::hasColumn('staycations', 'included_guest_capacity');

        $staycationData = [
            'name' => $request->name,
            'days' => $request->days,
            'type' => $type,
            'capacity' => $capacity,
            'adults' => $request->adults,
            'children' => $request->children,
            'image' => $imagePath,
            'price' => 0,
        ];
        if ($supportsMaxGuestCapacity) {
            $staycationData['max_guest_capacity'] = $maxGuestCapacity;
        }
        if ($supportsIncludedGuestCapacity) {
            $staycationData['included_guest_capacity'] = $includedGuestCapacity;
        }

        if (!empty($priceConfigs)) {
            $staycationData['price_configs'] = $priceConfigs;
        }

        $staycation = DB::transaction(function () use ($staycationData, $request) {
            $staycation = Staycation::create($staycationData);
            if ($request->exists('description')) {
                $this->saveRoomDescription($staycation, $request->input('description'));
            }

            return $staycation;
        });

        return response()->json([
            'message' => 'Room added successfully.',
            'id' => $staycation->id,
            'staycation' => [
                'id' => $staycation->id,
                'name' => $staycation->name,
                'room_description' => $staycation->resolveRoomDescription(),
                'days' => $staycation->days,
                'capacity' => $staycation->capacity,
                'max_guest_capacity' => $staycation->max_guest_capacity ?? $maxGuestCapacity ?? $capacity,
                'included_guest_capacity' => $staycation->included_guest_capacity ?? $includedGuestCapacity ?? $capacity,
                'adults' => $staycation->adults,
                'children' => $staycation->children,
                'image' => $staycation->image,
                'price_configs' => $staycation->price_configs,
                'price' => $staycation->price,
                'type' => $staycation->type,
                'sort_order' => $staycation->sort_order,
                'created_at' => $staycation->created_at
            ]
        ]);
    }

    public function show(Staycation $staycation_list)
    {
        return response()->json($staycation_list);
    }

    public function update(Request $request, Staycation $staycation_list)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:staycations,name,' . $staycation_list->id,
            'days' => 'required|integer|min:1',
            'adults' => 'required|integer|min:0',
            'children' => 'required|integer|min:0',
            'max_guest_capacity' => 'nullable|integer|min:1|max:30',
            'included_guest_capacity' => 'nullable|integer|min:1|max:30',
            'description' => 'nullable|string|max:1000',
            'priceConfigs' => 'nullable|string', // JSON string from frontend
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'remove_image' => 'nullable|in:1',
        ]);

        $defaultIncludedCapacity = max(1, (int) $request->adults);
        $includedGuestCapacity = max(1, (int)($request->input('included_guest_capacity', $defaultIncludedCapacity)));
        $capacity = $includedGuestCapacity;
        $requestedMaxGuestCapacity = (int)($request->input('max_guest_capacity', 0));
        $maxGuestCapacity = max($includedGuestCapacity, $requestedMaxGuestCapacity);
        if ($maxGuestCapacity <= 0) {
            $maxGuestCapacity = null;
        }

        // Determine type based on name
        $name = strtolower($request->name);
        $type = 'glamping'; // default
        if (str_contains($name, 'byot') || str_contains($name, 'car camping') || str_contains($name, 'bring your own')) {
            $type = 'camping';
        }

        // Glamping rooms must always have at least 1 adult.
        if ($type === 'glamping' && (int) $request->adults < 1) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => [
                    'adults' => ['Glamping rooms must have at least 1 adult.']
                ]
            ], 422);
        }

        // Handle price configs
        $priceConfigs = [];
        if ($request->filled('priceConfigs')) {
            $decodedConfigs = is_string($request->priceConfigs)
                ? json_decode($request->priceConfigs, true)
                : $request->priceConfigs;

            if (!is_array($decodedConfigs)) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => [
                        'priceConfigs' => ['Pricing rules must be a valid JSON array.']
                    ]
                ], 422);
            }

            [$priceConfigs, $priceConfigErrors] = $this->normalizePriceConfigs($decodedConfigs);

            if (!empty($priceConfigErrors)) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $priceConfigErrors
                ], 422);
            }

            if (empty($priceConfigs)) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => [
                        'priceConfigs' => ['Please add at least one pricing rule.']
                    ]
                ], 422);
            }
        }

        // Handle image upload
        $supportsMaxGuestCapacity = Schema::hasColumn('staycations', 'max_guest_capacity');
        $supportsIncludedGuestCapacity = Schema::hasColumn('staycations', 'included_guest_capacity');

        $updateData = [
            'name' => $request->name,
            'days' => $request->days,
            'type' => $type,
            'capacity' => $capacity,
            'adults' => $request->adults,
            'children' => $request->children,
            'price' => 0,
        ];
        if ($supportsMaxGuestCapacity) {
            $updateData['max_guest_capacity'] = $maxGuestCapacity;
        }
        if ($supportsIncludedGuestCapacity) {
            $updateData['included_guest_capacity'] = $includedGuestCapacity;
        }

        if (!empty($priceConfigs)) {
            $updateData['price_configs'] = $priceConfigs;
        }

        if ($request->has('remove_image') && $request->remove_image == '1') {
            // Clear image from database
            // Note: Cloudinary asset will remain but won't be referenced
            $updateData['image'] = null;
        } elseif ($request->hasFile('image')) {
            try {
                $updateData['image'] = $this->uploadImage($request->file('image'));
            } catch (\Throwable $e) {
                Log::error('Image update failed:', ['message' => $e->getMessage()]);
                return response()->json([
                    'message' => 'Image upload failed: ' . $e->getMessage(),
                    'errors' => ['image' => ['Failed to upload image to Cloudinary: ' . $e->getMessage()]]
                ], 422);
            }
        } else {
            // If no new image uploaded, keep existing image
            $updateData['image'] = $staycation_list->image;
        }

        DB::transaction(function () use ($staycation_list, $updateData, $request) {
            $staycation_list->update($updateData);
            if ($request->exists('description')) {
                $this->saveRoomDescription($staycation_list, $request->input('description'));
            }
        });

        // Return updated data for table refresh
        $updatedStaycation = $staycation_list->fresh('texts');

        return response()->json([
            'message' => 'Room updated successfully.',
            'staycation' => [
                'id' => $updatedStaycation->id,
                'name' => $updatedStaycation->name,
                'room_description' => $updatedStaycation->resolveRoomDescription(),
                'days' => $updatedStaycation->days,
                'capacity' => $updatedStaycation->capacity,
                'max_guest_capacity' => $updatedStaycation->max_guest_capacity ?? $maxGuestCapacity ?? $capacity,
                'included_guest_capacity' => $updatedStaycation->included_guest_capacity ?? $includedGuestCapacity ?? $capacity,
                'adults' => $updatedStaycation->adults,
                'children' => $updatedStaycation->children,
                'image' => $updatedStaycation->image,
                'price_configs' => $updatedStaycation->price_configs,
                'price' => $updatedStaycation->price,
                'type' => $updatedStaycation->type,
                'sort_order' => $updatedStaycation->sort_order,
                'created_at' => $updatedStaycation->created_at,
                'updated_at' => $updatedStaycation->updated_at
            ]
        ]);
    }

    private function saveRoomDescription(Staycation $staycation, ?string $description): void
    {
        StaycationText::updateOrCreate(
            [
                'staycation_id' => $staycation->id,
                'section' => 'description',
            ],
            [
                'title' => 'Room Description',
                'content' => trim((string) $description),
            ]
        );

        $staycation->unsetRelation('texts');
        $staycation->load('texts');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ordered_ids' => ['required', 'array', 'min:1'],
            'ordered_ids.*' => ['integer', 'distinct', 'exists:staycations,id'],
        ]);

        $orderedIds = array_values(array_unique(array_map('intval', $validated['ordered_ids'])));
        $currentIds = Staycation::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $missingIds = array_values(array_diff($currentIds, $orderedIds));
        $finalIds = array_merge($orderedIds, $missingIds);

        DB::transaction(function () use ($finalIds) {
            foreach ($finalIds as $index => $staycationId) {
                Staycation::whereKey($staycationId)->update([
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Staycation order updated successfully.',
        ]);
    }

    public function destroy(Staycation $staycation_list)
    {
        $staycation_list->delete();
        return redirect()->back()->with('success', 'Room deleted successfully!');
    }

    private function normalizePriceConfigs(array $decodedConfigs): array
    {
        $priceConfigs = [];
        $errors = [];
        $dateCoverageByIndex = [];

        foreach ($decodedConfigs as $index => $config) {
            if (!is_array($config)) {
                continue;
            }

            $normalized = [];

            $rates = $config['rates'] ?? null;
            if (is_array($rates)) {
                $normalized['rates'] = [
                    'nonPeak' => (float) ($rates['nonPeak'] ?? 0),
                    'peak' => (float) ($rates['peak'] ?? 0),
                    'superPeak' => (float) ($rates['superPeak'] ?? 0),
                ];
            } elseif (isset($config['nonPeak']) || isset($config['peak']) || isset($config['superPeak'])) {
                $normalized['rates'] = [
                    'nonPeak' => (float) ($config['nonPeak'] ?? 0),
                    'peak' => (float) ($config['peak'] ?? 0),
                    'superPeak' => (float) ($config['superPeak'] ?? 0),
                ];
            }

            $name = trim((string) ($config['name'] ?? ''));
            if ($name !== '') {
                $normalized['name'] = $name;
            }

            $type = strtolower(trim((string) ($config['type'] ?? 'day')));
            if (!in_array($type, ['day', 'date', 'range'], true)) {
                $type = 'day';
            }
            $normalized['type'] = $type;

            $dayPreset = strtolower(trim((string) ($config['dayPreset'] ?? 'custom')));
            if (in_array($dayPreset, ['custom', 'weekdays', 'weekends', 'all'], true)) {
                $normalized['dayPreset'] = $dayPreset;
            }

            if ($type === 'day') {
                $days = $config['days'] ?? [];
                if (!is_array($days)) {
                    $days = [];
                }
                $days = array_values(array_filter(array_map(static fn ($day) => trim((string) $day), $days)));

                if (empty($days)) {
                    $presetDays = $this->getDayPresetDays($dayPreset);
                    if (!empty($presetDays)) {
                        $days = $presetDays;
                    }
                }

                if (empty($days)) {
                    $errors["priceConfigs.$index.days"] = ['Select at least one day for this pricing rule.'];
                } else {
                    $normalized['days'] = $days;
                }
            } elseif ($type === 'range') {
                $startDate = trim((string) ($config['startDate'] ?? $config['start_date'] ?? ''));
                $endDate = trim((string) ($config['endDate'] ?? $config['end_date'] ?? ''));
                $days = $config['days'] ?? [];
                if (!is_array($days)) {
                    $days = [];
                }
                $days = array_values(array_filter(array_map(static fn ($day) => trim((string) $day), $days)));
                if (empty($days)) {
                    $presetDays = $this->getDayPresetDays($dayPreset);
                    if (!empty($presetDays)) {
                        $days = $presetDays;
                    }
                }

                if ($startDate === '' || $endDate === '') {
                    $errors["priceConfigs.$index.dateRange"] = ['Start date and end date are required for date range pricing.'];
                } elseif ($startDate > $endDate) {
                    $errors["priceConfigs.$index.dateRange"] = ['Start date must be before or equal to end date.'];
                } else {
                    $normalized['startDate'] = $startDate;
                    $normalized['endDate'] = $endDate;
                    if (!empty($days)) {
                        $normalized['days'] = $days;
                    }
                }
            } else {
                $dates = $this->extractPriceConfigDates($config);
                if (empty($dates)) {
                    $errors["priceConfigs.$index.dates"] = ['Select at least one date for this pricing rule.'];
                } else {
                    $normalized['dates'] = count($dates) === 1 ? $dates[0] : implode(', ', $dates);
                    if (count($dates) > 1) {
                        $normalized['multipleDates'] = true;
                    }
                }
            }

            if (!empty($normalized['rates'])) {
                $coverage = $this->buildPriceConfigDateCoverage($normalized);
                if (!empty($coverage)) {
                    foreach ($dateCoverageByIndex as $existingIndex => $existingCoverage) {
                        if ($this->priceConfigCoverageOverlaps($coverage, $existingCoverage)) {
                            $existingLabel = $priceConfigs[$existingIndex]['name'] ?? 'an existing pricing rule';
                            $errors["priceConfigs.$index.dayPreset"] = [
                                "This quick select overlaps with \"{$existingLabel}\". Please choose a different day pattern."
                            ];
                            continue 2;
                        }
                    }

                    $dateCoverageByIndex[$index] = $coverage;
                }

                $priceConfigs[] = $normalized;
            }
        }

        return [$priceConfigs, $errors];
    }

    private function buildPriceConfigDateCoverage(array $config): ?array
    {
        $type = strtolower(trim((string) ($config['type'] ?? 'day')));

        if ($type === 'date') {
            $dates = $this->extractPriceConfigDates($config);
            if (empty($dates)) {
                return null;
            }

            return array_fill_keys($dates, true);
        }

        if ($type === 'range') {
            $startDate = trim((string) ($config['startDate'] ?? $config['start_date'] ?? ''));
            $endDate = trim((string) ($config['endDate'] ?? $config['end_date'] ?? ''));
            if ($startDate === '' || $endDate === '') {
                return null;
            }

            $days = $config['days'] ?? [];
            if (!is_array($days)) {
                $days = [];
            }
            $days = array_values(array_filter(array_map(static fn ($day) => trim((string) $day), $days)));
            $allowedDays = !empty($days) ? array_fill_keys($days, true) : null;

            try {
                $coverage = [];
                $current = \Carbon\Carbon::parse($startDate)->startOfDay();
                $end = \Carbon\Carbon::parse($endDate)->startOfDay();

                while ($current->lte($end)) {
                    $dayOfWeek = $current->format('D');
                    if ($allowedDays === null || isset($allowedDays[$dayOfWeek])) {
                        $coverage[$current->format('Y-m-d')] = true;
                    }
                    $current->addDay();
                }

                return empty($coverage) ? null : $coverage;
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    private function priceConfigCoverageOverlaps(array $currentCoverage, array $existingCoverage): bool
    {
        return !empty(array_intersect_key($currentCoverage, $existingCoverage));
    }

    private function getDayPresetDays(string $dayPreset): array
    {
        return match ($dayPreset) {
            'weekdays' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu'],
            'weekends' => ['Fri', 'Sat'],
            'all' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            default => [],
        };
    }

    private function extractPriceConfigDates(array $config): array
    {
        $rawDates = $config['dates'] ?? [];

        if (is_array($rawDates)) {
            $dates = $rawDates;
        } else {
            $dates = preg_split('/[,\n]+/', (string) $rawDates) ?: [];
        }

        $normalized = [];
        foreach ($dates as $date) {
            $date = trim((string) $date);
            if ($date === '') {
                continue;
            }

            try {
                $normalized[] = \Carbon\Carbon::parse($date)->format('Y-m-d');
            } catch (\Throwable $e) {
                // Skip invalid date values and continue normalizing the rest.
            }
        }

        return array_values(array_unique($normalized));
    }
}
