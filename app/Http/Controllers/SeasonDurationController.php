<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DayType;
use App\Models\DayTypeDay;
use App\Models\PeakType;
use App\Models\PeakPeriod;
use App\Models\Holiday;
use App\Events\PricingUpdated;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class SeasonDurationController extends Controller
{
    /**
     * Show the season duration management page
     * 
     * This displays:
     * - Season durations (Non-Peak/Peak/Super-Peak with multiple date ranges)
     */
    public function index()
    {
        $peakTypes = PeakType::with(['periods' => function($query) {
            $query->orderBy('start_date', 'asc');
        }])->orderBy('priority', 'desc')->get();
        return view('admin.season-durations.index', compact('peakTypes'));
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

        $validated = $validator->validated();
        $peakPeriod->update($validated);

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

    public function getHolidays()
    {
        $holidays = Holiday::orderBy('date', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $holidays
        ]);
    }

    public function getWeekendConfig()
    {
        $weekendId = DayType::where('name', 'Weekend')->value('id');
        $weekendDays = DayTypeDay::where('day_type_id', $weekendId)->pluck('weekday')->toArray();

        return response()->json([
            'success' => true,
            'weekend_days' => $weekendDays
        ]);
    }
}
