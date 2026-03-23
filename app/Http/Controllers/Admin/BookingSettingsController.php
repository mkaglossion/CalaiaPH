<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\BookingSetting;

class BookingSettingsController extends Controller
{
    /**
     * Show booking settings form
     */
    public function index()
    {
        $settings = BookingSetting::whereIn('key', ['pet_price', 'max_pets_per_room'])->get();

        return view('admin.booking-settings.index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update booking settings
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'pet_price' => 'required|numeric|min:0',
                'max_pets_per_room' => 'required|integer|min:1|max:999',
            ]);

            $settings = [
                'pet_price' => [
                    'label' => 'Pet Fee',
                    'description' => 'One-time pet fee per booking (₱)',
                    'category' => 'pricing',
                ],
                'max_pets_per_room' => [
                    'label' => 'Maximum Pets Per Room',
                    'description' => 'Maximum number of pets allowed per room',
                    'category' => 'policy',
                ],
            ];

            foreach ($settings as $key => $config) {
                BookingSetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'label' => $config['label'],
                        'value' => (int) $validated[$key], // Ensure integer conversion
                        'description' => $config['description'],
                        'category' => $config['category'],
                    ]
                );
            }

            // Clear cache if applicable
            Cache::forget('booking_settings');

            // Return appropriate response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking settings updated successfully!'
                ]);
            }

            return redirect()->route('admin.booking-settings.index')
                ->with('success', 'Booking settings updated successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
