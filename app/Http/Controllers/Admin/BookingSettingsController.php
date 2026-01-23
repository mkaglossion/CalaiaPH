<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingSetting;

class BookingSettingsController extends Controller
{
    /**
     * Show booking settings form
     */
    public function index()
    {
        $settings = BookingSetting::where('category', 'pricing')->get();
        
        return view('admin.booking-settings.index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update booking settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'mattress_price_per_night' => 'required|numeric|min:0',
            'pet_price' => 'required|numeric|min:0',
            'weekday_sun_rate' => 'required|numeric|min:0',
            'weekday_mon_rate' => 'required|numeric|min:0',
            'weekday_tue_rate' => 'required|numeric|min:0',
            'weekday_wed_rate' => 'required|numeric|min:0',
            'weekday_thu_rate' => 'required|numeric|min:0',
            'weekday_fri_rate' => 'required|numeric|min:0',
            'weekday_sat_rate' => 'required|numeric|min:0',
            'weekend_rate' => 'required|numeric|min:0',
            'holiday_rate' => 'required|numeric|min:0',
            'peak_rate' => 'required|numeric|min:0',
            'super_peak_rate' => 'required|numeric|min:0',
            'standard_glamping_rate' => 'required|numeric|min:0',
            'premium_glamping_rate' => 'required|numeric|min:0',
            'dome_tent_rate' => 'required|numeric|min:0',
            'bell_tent_rate' => 'required|numeric|min:0',
            'byot_rate' => 'required|numeric|min:0',
            'car_camping_rate' => 'required|numeric|min:0',
        ]);

        $settings = [
            'mattress_price_per_night' => [
                'label' => 'Extra Mattress Price',
                'description' => 'Price per extra mattress per night (₱)',
            ],
            'pet_price' => [
                'label' => 'Pet Fee',
                'description' => 'One-time pet fee per booking (₱)',
            ],
            'weekday_sun_rate' => [
                'label' => 'Weekday Sunday Rate',
                'description' => 'Base rate for Sundays (₱)',
            ],
            'weekday_mon_rate' => [
                'label' => 'Weekday Monday Rate',
                'description' => 'Base rate for Mondays (₱)',
            ],
            'weekday_tue_rate' => [
                'label' => 'Weekday Tuesday Rate',
                'description' => 'Base rate for Tuesdays (₱)',
            ],
            'weekday_wed_rate' => [
                'label' => 'Weekday Wednesday Rate',
                'description' => 'Base rate for Wednesdays (₱)',
            ],
            'weekday_thu_rate' => [
                'label' => 'Weekday Thursday Rate',
                'description' => 'Base rate for Thursdays (₱)',
            ],
            'weekday_fri_rate' => [
                'label' => 'Weekday Friday Rate',
                'description' => 'Base rate for Fridays (₱)',
            ],
            'weekday_sat_rate' => [
                'label' => 'Weekday Saturday Rate',
                'description' => 'Base rate for Saturdays (₱)',
            ],
            'weekend_rate' => [
                'label' => 'Weekend Rate',
                'description' => 'Base rate for weekends (₱)',
            ],
            'holiday_rate' => [
                'label' => 'Holiday Rate',
                'description' => 'Base rate for holidays (₱)',
            ],
            'peak_rate' => [
                'label' => 'Peak Season Rate',
                'description' => 'Rate multiplier for peak season',
            ],
            'super_peak_rate' => [
                'label' => 'Super Peak Season Rate',
                'description' => 'Rate multiplier for super peak season',
            ],
            'standard_glamping_rate' => [
                'label' => 'Standard Glamping Rate',
                'description' => 'Base rate for Standard Glamping per night (₱)',
            ],
            'premium_glamping_rate' => [
                'label' => 'Premium Glamping Rate',
                'description' => 'Base rate for Premium Glamping per night (₱)',
            ],
            'dome_tent_rate' => [
                'label' => 'Dome Tent Rate',
                'description' => 'Base rate for Dome Tent per night (₱)',
            ],
            'bell_tent_rate' => [
                'label' => 'Bell Tent Rate',
                'description' => 'Base rate for Bell Tent per night (₱)',
            ],
            'byot_rate' => [
                'label' => 'BYOT Rate',
                'description' => 'Base rate for BYOT per slot (₱)',
            ],
            'car_camping_rate' => [
                'label' => 'Car Camping Rate',
                'description' => 'Base rate for Car Camping per slot (₱)',
            ],
        ];

        foreach ($settings as $key => $config) {
            BookingSetting::updateOrCreate(
                ['key' => $key],
                [
                    'label' => $config['label'],
                    'value' => $validated[$key],
                    'description' => $config['description'],
                    'category' => 'pricing',
                ]
            );
        }

        return redirect()->route('admin.booking-settings.index')
            ->with('success', 'Booking settings updated successfully!');
    }
}
