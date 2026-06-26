<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTour;
use App\Models\Package;
use App\Models\Staycation;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventoryManagementController extends Controller
{
    /**
     * Display unified inventory management dashboard
     */
    public function index(Request $request)
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::today();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::today()->addDays(30)->endOfDay();

        $staycations = Staycation::with(['inventories' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date');
        }])->get();
        
        $packages = Package::with(['inventory' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date');
        }])->get();
        
        $dayTours = DayTour::with(['inventory' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date');
        }])->where('is_active', true)->get();

        $staycationIds = $staycations->pluck('id')->all();
        
        $bookings = Booking::query()
            ->whereIn('booking_type', Booking::STAYCATION_TYPES)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereBetween('checkin_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->get();
        
        $packagesBookedByDateStaycation = [];
        
        foreach ($bookings as $booking) {
            $checkinDate = $booking->checkin_date instanceof Carbon
                ? $booking->checkin_date->toDateString()
                : Carbon::parse($booking->checkin_date)->toDateString();
            
            $bookingData = $booking->booking_data ?? [];
            $accommodations = $bookingData['accommodations'] ?? [];
            
            foreach ($accommodations as $accommodation) {
                $staycationId = $accommodation['room_id'] ?? $accommodation['id'] ?? null;
                if ($staycationId) {
                    $dateKey = $checkinDate;
                    $packagesBookedByDateStaycation[$staycationId][$dateKey] = ($packagesBookedByDateStaycation[$staycationId][$dateKey] ?? 0) + 1;
                }
            }
        }
        
        $staycations->each(function ($staycation) use ($packagesBookedByDateStaycation) {
            if ($staycation->inventories) {
                foreach ($staycation->inventories as $inventory) {
                    $dateKey = Carbon::parse($inventory->date)->toDateString();
                    $inventory->packages_booked = $packagesBookedByDateStaycation[$staycation->id][$dateKey] ?? 0;
                }
            }
        });

        // Pagination settings
        $itemsPerPage = 10;

        return view('admin.inventory.unified-management', compact('staycations', 'packages', 'dayTours', 'itemsPerPage', 'dateFrom', 'dateTo'));
    }
}
