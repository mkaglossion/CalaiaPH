<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\BookingSetting;

class BookingSettingsController extends Controller
{
    /**
     * Update mobile layout mode for booking steps (mobile only UI behavior).
     */
    public function updateMobileLayout(Request $request)
    {
        $validated = $request->validate([
            'setting_key' => 'required|string|in:mobile_layout_step1_availability,mobile_layout_step2_food,mobile_layout_step3_activities',
            'value' => 'required|integer|in:1,2,3',
        ]);

        $labels = [
            'mobile_layout_step1_availability' => 'Mobile Layout - Step 1 (Availability)',
            'mobile_layout_step2_food' => 'Mobile Layout - Step 2 (Food)',
            'mobile_layout_step3_activities' => 'Mobile Layout - Step 3 (Activities)',
        ];

        $descriptions = [
            'mobile_layout_step1_availability' => 'Mobile card layout mode for staycation availability step.',
            'mobile_layout_step2_food' => 'Mobile card layout mode for staycation food step.',
            'mobile_layout_step3_activities' => 'Mobile card layout mode for staycation activities step.',
        ];

        BookingSetting::updateOrCreate(
            ['key' => $validated['setting_key']],
            [
                'label' => $labels[$validated['setting_key']],
                'value' => (int) $validated['value'],
                'description' => $descriptions[$validated['setting_key']],
                'category' => 'policy',
            ]
        );

        Cache::forget('booking_settings');

        return response()->json([
            'success' => true,
            'message' => 'Mobile layout setting updated successfully.',
        ]);
    }

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
            // Backward-compatible branch: allow mobile layout updates on existing route.
            if ($request->has('setting_key')) {
                $validated = $request->validate([
                    'setting_key' => 'required|string|in:mobile_layout_step1_availability,mobile_layout_step2_food,mobile_layout_step3_activities',
                    'value' => 'required|integer|in:1,2,3',
                ]);

                $labels = [
                    'mobile_layout_step1_availability' => 'Mobile Layout - Step 1 (Availability)',
                    'mobile_layout_step2_food' => 'Mobile Layout - Step 2 (Food)',
                    'mobile_layout_step3_activities' => 'Mobile Layout - Step 3 (Activities)',
                ];

                $descriptions = [
                    'mobile_layout_step1_availability' => 'Mobile card layout mode for staycation availability step.',
                    'mobile_layout_step2_food' => 'Mobile card layout mode for staycation food step.',
                    'mobile_layout_step3_activities' => 'Mobile card layout mode for staycation activities step.',
                ];

                BookingSetting::updateOrCreate(
                    ['key' => $validated['setting_key']],
                    [
                        'label' => $labels[$validated['setting_key']],
                        'value' => (int) $validated['value'],
                        'description' => $descriptions[$validated['setting_key']],
                        'category' => 'policy',
                    ]
                );

                Cache::forget('booking_settings');

                return response()->json([
                    'success' => true,
                    'message' => 'Mobile layout setting updated successfully.',
                ]);
            }

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
