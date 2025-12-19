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

        // Array of weekend days (0=Sun, 6=Sat) for checkboxes
        $weekendDays = DayTypeDay::where('day_type_id', 2)->pluck('weekday')->toArray();

        return view('pricing-config.index', compact(
            'dayTypes', 'peakTypes', 'holidays', 'durationSets', 'pricingConfigs', 'weekendDays'
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

    // Update Weekday/Weekend
    public function updateDayTypes(Request $request)
    {
        $weekendDays = $request->input('weekend', []);

        // Update each day (0=Sun, 6=Sat)
        foreach(range(0,6) as $i) {
            $dayType = DayTypeDay::firstOrNew(['weekday' => $i]);
            $dayType->day_type_id = in_array($i, $weekendDays) ? 2 : 1; // 2=Weekend, 1=Weekday
            $dayType->save();
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
            'start_date.*' => 'required|date',
            'end_date.*' => 'required|date|after_or_equal:start_date.*'
        ]);

        foreach($request->start_date as $peakTypeId => $startDate) {
            if($startDate && $request->end_date[$peakTypeId]) {
                PeakPeriod::create([
                    'peak_type_id' => $peakTypeId,
                    'start_date' => $startDate,
                    'end_date' => $request->end_date[$peakTypeId]
                ]);
            }
        }

        return redirect()->back()->with('success', 'Peak periods saved successfully.');
    }
}
