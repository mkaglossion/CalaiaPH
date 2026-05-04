<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PromoCodesController extends Controller
{
    /**
     * Display a listing of promo codes.
     */
    public function index(Request $request)
    {
        // If it's an AJAX request, return JSON
        if ($request->ajax()) {
            try {
                $query = PromoCode::query();

                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }

                if ($request->filled('status')) {
                    $query->where('is_active', $request->status === 'active');
                }

                $promoCodes = $query->orderBy('created_at', 'desc')->paginate(15);

                return response()->json([
                    'data' => $promoCodes->items(),
                    'meta' => [
                        'current_page' => $promoCodes->currentPage(),
                        'last_page' => $promoCodes->lastPage(),
                        'per_page' => $promoCodes->perPage(),
                        'total' => $promoCodes->total(),
                    ],
                    'links' => [
                        'first' => $promoCodes->url(1),
                        'last' => $promoCodes->url($promoCodes->lastPage()),
                        'prev' => $promoCodes->previousPageUrl(),
                        'next' => $promoCodes->nextPageUrl(),
                    ],
                ]);
            } catch (\Exception $e) {
                // Handle case where table doesn't exist
                if (str_contains($e->getMessage(), 'promo_codes')) {
                    return response()->json([
                        'error' => 'Database table not found',
                        'message' => 'Please run: php artisan migrate',
                        'data' => [],
                        'meta' => [
                            'current_page' => 1,
                            'last_page' => 1,
                            'per_page' => 15,
                            'total' => 0,
                        ]
                    ], 200);
                }
                throw $e;
            }
        }

        // Return the view for regular requests
        return view('admin.promo-codes.index');
    }

    /**
     * Store a newly created promo code.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'min_booking_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        // Additional validation for discount values
        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return response()->json([
                'message' => 'Percentage discount cannot exceed 100%',
                'errors' => ['discount_value' => ['Percentage discount cannot exceed 100%']]
            ], 422);
        }

        try {
        try {
            $promoCode = PromoCode::create($validated);

            return response()->json([
                'message' => 'Promo code created successfully.',
                'promo_code' => $promoCode
            ], 201);
        } catch (\Exception $e) {
            // Handle case where table doesn't exist
            if (str_contains($e->getMessage(), 'promo_codes')) {
                return response()->json([
                    'error' => 'Database table not found',
                    'message' => 'Please run: php artisan migrate'
                ], 500);
            }
            throw $e;
        }
        } catch (\Exception $e) {
            // If database table doesn't exist, return a helpful message
            if (str_contains($e->getMessage(), 'promo_codes')) {
                return response()->json([
                    'message' => 'Database table not found. Please run: php artisan migrate',
                    'error' => 'Table does not exist'
                ], 500);
            }

            throw $e;
        }
    }

    /**
     * Display the specified promo code.
     */
    public function show(PromoCode $promoCode): JsonResponse
    {
        return response()->json($promoCode);
    }

    /**
     * Update the specified promo code.
     */
    public function update(Request $request, PromoCode $promoCode): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code,' . $promoCode->id,
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'min_booking_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        // Additional validation for discount values
        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return response()->json([
                'message' => 'Percentage discount cannot exceed 100%',
                'errors' => ['discount_value' => ['Percentage discount cannot exceed 100%']]
            ], 422);
        }

        $promoCode->update($validated);

        return response()->json([
            'message' => 'Promo code updated successfully.',
            'promo_code' => $promoCode->fresh()
        ]);
    }

    /**
     * Remove the specified promo code.
     */
    public function destroy(PromoCode $promoCode): JsonResponse
    {
        $promoCode->delete();

        return response()->json([
            'message' => 'Promo code deleted successfully.'
        ]);
    }

    /**
     * Toggle active status of promo code.
     */
    public function toggleStatus(PromoCode $promoCode): JsonResponse
    {
        $promoCode->update(['is_active' => !$promoCode->is_active]);

        return response()->json([
            'message' => 'Promo code status updated successfully.',
            'promo_code' => $promoCode->fresh()
        ]);
    }

    /**
     * Validate and apply promo code.
     */
    public function applyPromoCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'booking_total' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->code));
        $bookingTotal = (float) $request->booking_total;

        try {
            $promoCode = PromoCode::where('code', $code)->first();

            if (!$promoCode) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Promo code not found.'
                ], 404);
            }

            if (!$promoCode->is_active) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Promo code is not active.'
                ], 400);
            }

            $now = now();
            if ($now->lt($promoCode->start_date) || $now->gt($promoCode->end_date)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Promo code is not valid for the current date.'
                ], 400);
            }

            if ($promoCode->min_booking_amount && $bookingTotal < $promoCode->min_booking_amount) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Minimum booking amount of ₱' . number_format($promoCode->min_booking_amount, 2) . ' required.'
                ], 400);
            }

            if ($promoCode->usage_limit && $promoCode->usage_count >= $promoCode->usage_limit) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Promo code usage limit exceeded.'
                ], 400);
            }

            // Calculate discount
            $discount = 0;
            if ($promoCode->discount_type === 'percentage') {
                $discount = $bookingTotal * ($promoCode->discount_value / 100);
            } else {
                $discount = min($promoCode->discount_value, $bookingTotal);
            }

            $newTotal = max(0, $bookingTotal - $discount);

            return response()->json([
                'valid' => true,
                'message' => 'Promo code applied successfully.',
                'promo_code' => [
                    'id' => $promoCode->id,
                    'code' => $promoCode->code,
                    'name' => $promoCode->name,
                    'discount_type' => $promoCode->discount_type,
                    'discount_value' => $promoCode->discount_value,
                    'discount_amount' => $discount,
                ],
                'original_total' => $bookingTotal,
                'new_total' => $newTotal,
                'savings' => $discount,
            ]);

        } catch (\Exception $e) {
            // Handle case where table doesn't exist
            if (str_contains($e->getMessage(), 'promo_codes')) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Promo codes are not available at the moment.'
                ], 500);
            }
            throw $e;
        }
    }
}