<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DayType;
use App\Models\DayTypeDay; // for storing weekday/weekend mapping
use App\Models\PeakType;
use App\Models\PeakPeriod;
use App\Models\Holiday;
use App\Models\DurationSet;
use App\Models\PricingConfig;

class PricingConfigController extends Controller
{
    // Show all configurations
    public function index()
    {
        $dayTypes = DayType::with('days')->get();
        $peakTypes = PeakType::with('periods')->get();
        $holidays = Holiday::all();
        $durationSets = DurationSet::all();
        $pricingConfigs = PricingConfig::with(['peakPeriod', 'holiday', 'durationSet', 'dayType'])->get();

        // Get the IDs for Weekday and Weekend dynamically
        $weekdayId = DayType::where('name', 'Weekday')->value('id');
        $weekendId = DayType::where('name', 'Weekend')->value('id');

        // Array of weekend days (0=Sun, 6=Sat) for checkboxes
        $weekendDays = DayTypeDay::where('day_type_id', $weekendId)->pluck('weekday')->toArray();

        return view('pricing-config.index', compact(
            'dayTypes', 'peakTypes', 'holidays', 'durationSets', 'pricingConfigs', 'weekendDays', 'weekdayId', 'weekendId'
        ));
    }

    // Store new pricing config
    public function store(Request $request)
    {
        $request->validate([
            'peak_period_id' => 'nullable|exists:peak_periods,id',
            'holiday_id' => 'nullable|exists:holidays,id',
            'duration_set_id' => 'required|exists:duration_sets,id',
            'day_type_id' => 'required|exists:day_types,id',
            'price' => 'required|numeric|min:0'
        ]);

        PricingConfig::create($request->all());

        return redirect()->back()->with('success', 'Pricing config added successfully.');
    }

    // Update Weekday / Weekend mapping
    public function updateDayTypes(Request $request)
    {
        $weekendDays = $request->input('weekend', []);

        // Ensure DayType entries exist
        $weekday = DayType::firstOrCreate(['name' => 'Weekday']);
        $weekend = DayType::firstOrCreate(['name' => 'Weekend']);

        foreach (range(0, 6) as $i) {
            $dayTypeDay = DayTypeDay::firstOrNew(['weekday' => $i]);
            $dayTypeDay->day_type_id = in_array($i, $weekendDays) ? $weekend->id : $weekday->id;
            $dayTypeDay->save();
        }

        return redirect()->back()->with('success', 'Weekdays/Weekends updated successfully.');
    }

    // Store new holiday
    public function storeHoliday(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date'
        ]);

        Holiday::create($request->all());

        return redirect()->back()->with('success', 'Holiday added successfully.');
    }

    // Store peak period (Non-Peak / Peak / Super-Peak)
    public function storePeakPeriod(Request $request)
    {
        $request->validate([
            'peak_type_id' => 'required|exists:peak_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $period = PeakPeriod::create([
            'peak_type_id' => $request->peak_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Peak period added successfully',
                'period' => $period
            ]);
        }

        return redirect()->back()->with('success', 'Peak period added successfully.');
    }

}
