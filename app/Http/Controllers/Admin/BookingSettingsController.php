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
        $settings = BookingSetting::whereIn('key', ['mattress_price_per_night', 'pet_price'])->get();

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
