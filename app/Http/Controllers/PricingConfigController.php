<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DayType;
use App\Models\DayTypeDay;
use App\Models\PeakType;
use App\Models\PeakPeriod;
use App\Models\Holiday;
use App\Models\DurationSet;
use App\Models\PricingConfig;
use App\Events\PricingUpdated;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PricingConfigController extends Controller
{
    /**
     * Show all pricing configurations
     * 
     * This displays:
     * - Weekend Rules (checkboxes for Fri-Sat)
     * - Durations (Non-Peak/Peak/Super-Peak with multiple date ranges)
     * - Declared Holidays
     * - Rate Matrix (unit_type, season_type, day_type)
     */
    public function index()
    {
        $dayTypes = DayType::with('days')->get();
        $peakTypes = PeakType::with(['periods' => function($query) {
            $query->orderBy('start_date', 'asc');
        }])->orderBy('priority', 'desc')->get();
        $holidays = Holiday::orderBy('date', 'asc')->get();
        $durationSets = DurationSet::all();
        
        // Get pricing configs grouped by unit_type for the rate matrix
        $pricingConfigs = PricingConfig::with(['peakType', 'dayType', 'durationSet'])
            ->get()
            ->groupBy('unit_type');

        // Get weekend days configuration
        $weekendId = DayType::where('name', 'Weekend')->value('id');
        $weekendDays = DayTypeDay::where('day_type_id', $weekendId)->pluck('weekday')->toArray();

        // Default to Fri (5) and Sat (6) if no configuration exists
        if (empty($weekendDays)) {
            $weekendDays = [5, 6];
        }

        return view('admin.pricingConfig.index', compact(
            'dayTypes', 'peakTypes', 'holidays', 'durationSets', 
            'pricingConfigs', 'weekendDays'
        ));
    }

    /**
     * Store new pricing config in the rate matrix
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_type' => 'required|string|max:255',
            'peak_type_id' => 'required|exists:peak_types,id',
            'day_type_id' => 'required|exists:day_types,id',
            'price' => 'required|numeric|min:0'
        ]);

        // Get the default duration set (Nightly Rate)
        $durationSet = DurationSet::where('name', 'Nightly Rate')->first();
        if (!$durationSet) {
            $error = 'Default duration set not found.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $error], 500);
            }
            return redirect()->back()->withErrors(['duration_set' => $error]);
        }

        $validated['duration_set_id'] = $durationSet->id;

        $pricingConfig = PricingConfig::create($validated);

        // Broadcast pricing update
        broadcast(new PricingUpdated($validated['unit_type']))->toOthers();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pricing config added successfully.',
                'pricing_config' => $pricingConfig
            ]);
        }

        return redirect()->back()->with('success', 'Pricing config added successfully.');
    }

    /**
     * Update Weekend/Weekday configuration
     * 
     * Weekend = Fri (5) and Sat (6) by default
     * Weekday = Sun (0), Mon (1), Tue (2), Wed (3), Thu (4)
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDayTypes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'weekend_days' => 'required|array',
            'weekend_days.*' => 'integer|between:0,6'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $weekendDays = $request->input('weekend_days', []);

        // Ensure DayType entries exist
        $weekday = DayType::firstOrCreate(['name' => 'Weekday']);
        $weekend = DayType::firstOrCreate(['name' => 'Weekend']);

        // Update each weekday (0-6) mapping
        foreach (range(0, 6) as $i) {
            $dayTypeDay = DayTypeDay::firstOrNew(['weekday' => $i]);
            $dayTypeDay->day_type_id = in_array($i, $weekendDays) ? $weekend->id : $weekday->id;
            $dayTypeDay->save();
        }

        // Broadcast pricing update for all unit types
        broadcast(new PricingUpdated())->toOthers();

        return redirect()->back()->with('success', 'Weekdays/Weekends updated successfully.');
    }

    /**
     * Store a new declared holiday
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function storeHoliday(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date'
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Holiday::create($validator->validated());

        // Broadcast pricing update for all unit types
        broadcast(new PricingUpdated())->toOthers();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Holiday added successfully.',
                'holiday' => Holiday::latest()->first()
            ]);
        }

        return redirect()->back()->with('success', 'Holiday added successfully.');
    }

    /**
     * Update a holiday
     * 
     * @param Request $request
     * @param Holiday $holiday
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateHoliday(Request $request, Holiday $holiday)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date,' . $holiday->id
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $holiday->update($validator->validated());

        // Broadcast pricing update for all unit types
        broadcast(new PricingUpdated())->toOthers();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Holiday updated successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Holiday updated successfully.');
    }

    /**
     * Delete a holiday
     * 
     * @param Holiday $holiday
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyHoliday(Holiday $holiday)
    {
        $holiday->delete();

        // Broadcast pricing update for all unit types
        broadcast(new PricingUpdated())->toOthers();

        return redirect()->back()->with('success', 'Holiday deleted successfully.');
    }

    /**
     * Store a new peak period (Duration)
     * 
     * Supports multiple date ranges per type:
     * - Non-Peak: Jun 16 – Aug 31
     * - Peak: Jan 5 – Jun 15, Sep 1 – Dec 19
     * - Super-Peak: Apr 2 – Apr 6 (Holy Week), Oct 31 – Nov 2 (Halloween), 
     *               Dec 20 – Dec 31 & Jan 1 – Jan 4 (Holiday Season - split as Option A)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function storePeakPeriod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'peak_type_id' => 'required|exists:peak_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'name' => 'nullable|string|max:255' // Optional: Holy Week, Halloween, etc.
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $overlapError = $this->checkPeakPeriodOverlap(
            $request->start_date,
            $request->end_date
        );

        if ($overlapError) {
            $message = 'Invalid date range. This schedule overlaps with an existing season range.';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => [
                        'date_range' => [$message],
                    ],
                ], 422);
            }

            return redirect()->back()->with('error', $message)->withInput();
        }

        $period = PeakPeriod::create([
            'peak_type_id' => $request->peak_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'name' => $request->name
        ]);

        // Broadcast pricing update for all unit types
        broadcast(new PricingUpdated())->toOthers();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Peak period added successfully.',
                'period' => $period,
            ]);
        }

        return redirect()->back()->with('success', 'Peak period added successfully.');
    }

    /**
     * Update a peak period
     * 
     * @param Request $request
     * @param PeakPeriod $peakPeriod
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updatePeakPeriod(Request $request, PeakPeriod $peakPeriod)
    {
        $validator = Validator::make($request->all(), [
            'peak_type_id' => 'required|exists:peak_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'name' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $overlapError = $this->checkPeakPeriodOverlap(
            $request->start_date,
            $request->end_date,
            $peakPeriod->id
        );

        if ($overlapError) {
            $message = 'Invalid date range. This schedule overlaps with an existing season range.';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => [
                        'date_range' => [$message],
                    ],
                ], 422);
            }

            return redirect()->back()->with('error', $message)->withInput();
        }

        $peakPeriod->update($validator->validated());

        // Broadcast pricing update for all unit types
        broadcast(new PricingUpdated())->toOthers();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Peak period updated successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Peak period updated successfully.');
    }

    /**
     * Delete a peak period
     * 
     * @param PeakPeriod $peakPeriod
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyPeakPeriod(PeakPeriod $peakPeriod)
    {
        $peakPeriod->delete();

        // Broadcast pricing update for all unit types
        broadcast(new PricingUpdated())->toOthers();

        return redirect()->back()->with('success', 'Peak period deleted successfully.');
    }

    /**
     * Check for overlapping peak periods.
     *
     * Overlapping season ranges are rejected to keep pricing unambiguous.
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $ignoreId
     * @return string|null
     */
    private function checkPeakPeriodOverlap($startDate, $endDate, $ignoreId = null)
    {
        $newStart = Carbon::parse($startDate);
        $newEnd = Carbon::parse($endDate);

        $overlaps = PeakPeriod::where(function($query) use ($newStart, $newEnd) {
                $query->where(function($q) use ($newStart, $newEnd) {
                    $q->where('start_date', '<=', $newEnd)
                      ->where('end_date', '>=', $newStart);
                });
            })
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->with('peakType')
            ->orderBy('start_date')
            ->get();

        if ($overlaps->isEmpty()) {
            return null;
        }

        $overlapLabels = $overlaps->map(function ($period) {
            $label = $period->peakType->name ?? 'Season';
            if (!empty($period->name)) {
                $label .= ' (' . $period->name . ')';
            }

            return $label . ' [' . $period->start_date . ' to ' . $period->end_date . ']';
        })->implode(', ');

        return "This date range overlaps with existing season range(s): {$overlapLabels}.";
    }

    /**
     * Calculate total price for a booking
     * 
     * This is the CORE pricing decision rule:
     * 1. For each night (check-in inclusive, check-out exclusive):
     *    a. Determine Season Type (Priority: Super-Peak > Peak > Non-Peak)
     *    b. Determine Day Type (Holiday > Weekend > Weekday)
     *    c. Get rate from price_matrix[unit_type][season_type][day_type]
     * 2. Sum all nightly rates
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculatePrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_type' => 'required|string|max:255',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $unitType = $request->unit_type;
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        
        $pricingConfigs = PricingConfig::where('unit_type', $unitType)
            ->with(['peakType', 'dayType'])
            ->get();

        if ($pricingConfigs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No pricing configuration found for unit type: ' . $unitType
            ], 404);
        }

        // Build price lookup table
        $priceMatrix = [];
        foreach ($pricingConfigs as $config) {
            $priceMatrix[$config->peakType->name][$config->dayType->name] = $config->price;
        }

        $totalPrice = 0;
        $breakdown = [];

        // Get holiday dates for the booking period
        $holidayDates = Holiday::whereBetween('date', [$checkIn, $checkOut->copy()->subDay()])
            ->pluck('date')
            ->map(function($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();

        // Get weekend configuration
        $weekendId = DayType::where('name', 'Weekend')->value('id');
        $weekendDayIds = DayTypeDay::where('day_type_id', $weekendId)->pluck('weekday')->toArray();

        // Get peak type priorities (higher number = higher priority)
        $peakTypePriorities = PeakType::pluck('priority', 'name')->toArray();

        // Calculate price per night
        $current = $checkIn->copy();
        while ($current->lt($checkOut)) {
            $nightDate = $current->format('Y-m-d');
            
            // Step A: Determine Season Type (Duration)
            $seasonType = $this->determineSeasonType($current, $peakTypePriorities);
            
            // Step B: Determine Day Type
            $dayType = $this->determineDayType($current, $holidayDates, $weekendDayIds);
            
            // Step C: Get Rate
            $nightlyRate = $priceMatrix[$seasonType][$dayType] ?? null;
            
            if ($nightlyRate === null) {
                return response()->json([
                    'success' => false,
                    'message' => "No rate found for {$unitType} | {$seasonType} | {$dayType} on {$nightDate}"
                ], 404);
            }

            $totalPrice += $nightlyRate;
            $breakdown[] = [
                'date' => $nightDate,
                'season_type' => $seasonType,
                'day_type' => $dayType,
                'rate' => $nightlyRate
            ];

            $current->addDay();
        }

        return response()->json([
            'success' => true,
            'unit_type' => $unitType,
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'nights' => count($breakdown),
            'total_price' => $totalPrice,
            'breakdown' => $breakdown
        ]);
    }

    /**
     * Determine the season type for a given date
     * 
     * Priority: Super-Peak > Peak > Non-Peak
     * If no configured period matches, returns null (no pricing)
     * 
     * @param Carbon $date
     * @param array $priorities
     * @return string|null
     */
    private function determineSeasonType($date, $priorities)
    {
        $dateStr = $date->format('Y-m-d');
        
        // Find all matching periods
        $matchingPeriods = PeakPeriod::where(function($query) use ($dateStr) {
                $query->where(function($q) use ($dateStr) {
                    $q->where('start_date', '<=', $dateStr)
                      ->where('end_date', '>=', $dateStr);
                });
            })
            ->with('peakType')
            ->get();

        if ($matchingPeriods->isEmpty()) {
            return null; // No season configured for this date
        }

        // Return the period with highest priority
        $highestPriority = -999;
        $selectedType = null;

        foreach ($matchingPeriods as $period) {
            $priority = $priorities[$period->peakType->name] ?? 0;
            if ($priority > $highestPriority) {
                $highestPriority = $priority;
                $selectedType = $period->peakType->name;
            }
        }

        return $selectedType;
    }

    /**
     * Determine the day type for a given date
     * 
     * Priority: Holiday > Weekend > Weekday
     * 
     * @param Carbon $date
     * @param array $holidayDates
     * @param array $weekendDayIds
     * @return string
     */
    private function determineDayType($date, $holidayDates, $weekendDayIds)
    {
        $dateStr = $date->format('Y-m-d');
        $dayOfWeek = (int) $date->dayOfWeek; // 0=Sun, 1=Mon, ..., 6=Sat

        // 1. Check if it's a declared holiday
        if (in_array($dateStr, $holidayDates)) {
            return 'Holiday';
        }

        // 2. Check if it's a weekend
        if (in_array($dayOfWeek, $weekendDayIds)) {
            return 'Weekend';
        }

        // 3. Otherwise, it's a weekday
        return 'Weekday';
    }

    /**
     * Seed initial peak types with priorities
     * 
     * Run this once to set up the peak types:
     * - Super-Peak: priority 3 (highest)
     * - Peak: priority 2
     * - Non-Peak: priority 1
     * 
     * @return void
     */
    public function seedPeakTypes()
    {
        $peakTypes = [
            ['name' => 'Super-Peak', 'priority' => 3],
            ['name' => 'Peak', 'priority' => 2],
            ['name' => 'Non-Peak', 'priority' => 1],
        ];

        foreach ($peakTypes as $type) {
            PeakType::updateOrCreate(
                ['name' => $type['name']],
                ['priority' => $type['priority']]
            );
        }

        return redirect()->back()->with('success', 'Peak types seeded successfully.');
    }

    /**
     * API: Get all peak types
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPeakTypes()
    {
        $peakTypes = PeakType::with(['periods' => function($query) {
            $query->orderBy('start_date', 'asc');
        }])->orderBy('priority', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $peakTypes
        ]);
    }

    /**
     * API: Get all holidays
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHolidays()
    {
        $holidays = Holiday::orderBy('date', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $holidays
        ]);
    }

    /**
     * API: Get weekend configuration
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWeekendConfig()
    {
        $weekendId = DayType::where('name', 'Weekend')->value('id');
        $weekendDays = DayTypeDay::where('day_type_id', $weekendId)->pluck('weekday')->toArray();

        return response()->json([
            'success' => true,
            'weekend_days' => $weekendDays
        ]);
    }

    /**
     * Update a pricing config
     *
     * @param Request $request
     * @param PricingConfig $pricingConfig
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, PricingConfig $pricingConfig)
    {
        $validator = Validator::make($request->all(), [
            'unit_type' => 'required|string|max:255',
            'peak_type_id' => 'required|exists:peak_types,id',
            'day_type_id' => 'required|exists:day_types,id',
            'price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // Ensure duration_set_id is set (should already be, but just in case)
        if (!$pricingConfig->duration_set_id) {
            $durationSet = DurationSet::where('name', 'Nightly Rate')->first();
            if ($durationSet) {
                $validated['duration_set_id'] = $durationSet->id;
            }
        }

        $pricingConfig->update($validated);

        // Broadcast pricing update
        broadcast(new PricingUpdated($validated['unit_type']))->toOthers();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pricing config updated successfully.',
                'pricing_config' => $pricingConfig
            ]);
        }

        return redirect()->back()->with('success', 'Pricing config updated successfully.');
    }

    /**
     * Delete a pricing config
     *
     * @param Request $request
     * @param PricingConfig $pricingConfig
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, PricingConfig $pricingConfig)
    {
        $unitType = $pricingConfig->unit_type;
        $pricingConfig->delete();

        // Broadcast pricing update
        broadcast(new PricingUpdated($unitType))->toOthers();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pricing config deleted successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Pricing config deleted successfully.');
    }
}
