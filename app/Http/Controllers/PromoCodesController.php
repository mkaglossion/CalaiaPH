<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
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
                $usagePromoId = $request->filled('promo_id') ? (int) $request->promo_id : null;
                $usageFrom = $request->filled('usage_from') ? $request->string('usage_from')->toString() : null;
                $usageTo = $request->filled('usage_to') ? $request->string('usage_to')->toString() : null;
                PromoCode::syncAutomaticStatus();

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
                $verifiedUsageCounts = $this->buildVerifiedUsageCounts();
                $analytics = $this->buildAnalytics($verifiedUsageCounts);
                $recentUsages = $this->buildRecentUsages($usagePromoId, $usageFrom, $usageTo);
                $selectedPromo = $usagePromoId ? PromoCode::find($usagePromoId) : null;
                $promoItems = collect($promoCodes->items())->map(function (PromoCode $promo) use ($verifiedUsageCounts) {
                    $verifiedUsageCount = (int) ($verifiedUsageCounts[$promo->id] ?? 0);
                    $remainingUses = $promo->usage_limit !== null
                        ? max(0, (int) $promo->usage_limit - $verifiedUsageCount)
                        : null;

                    return [
                        'id' => $promo->id,
                        'code' => $promo->code,
                        'name' => $promo->name,
                        'discount_type' => $promo->discount_type,
                        'discount_value' => $promo->discount_value,
                        'start_date' => $promo->start_date?->toDateString(),
                        'end_date' => $promo->end_date?->toDateString(),
                        'usage_limit' => $promo->usage_limit,
                        'usage_count' => $verifiedUsageCount,
                        'verified_usage_count' => $verifiedUsageCount,
                        'remaining_uses' => $remainingUses,
                        'is_active' => (bool) $promo->is_active,
                        'description' => $promo->description,
                        'created_at' => $promo->created_at?->toDateTimeString(),
                        'updated_at' => $promo->updated_at?->toDateTimeString(),
                    ];
                })->all();

                return response()->json([
                    'data' => $promoItems,
                    'analytics' => $analytics,
                    'recent_usages' => $recentUsages,
                    'usage_filters' => [
                        'from' => $usageFrom,
                        'to' => $usageTo,
                    ],
                    'selected_promo' => $selectedPromo ? [
                        'id' => $selectedPromo->id,
                        'code' => $selectedPromo->code,
                        'name' => $selectedPromo->name,
                        'usage_count' => (int) ($verifiedUsageCounts[$selectedPromo->id] ?? 0),
                        'usage_limit' => $selectedPromo->usage_limit,
                        'remaining_uses' => $selectedPromo->usage_limit !== null
                            ? max(0, (int) $selectedPromo->usage_limit - (int) ($verifiedUsageCounts[$selectedPromo->id] ?? 0))
                            : null,
                        'is_active' => (bool) $selectedPromo->is_active,
                    ] : null,
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
                        'analytics' => $this->emptyAnalytics(),
                        'recent_usages' => [],
                        'usage_filters' => [
                            'from' => null,
                            'to' => null,
                        ],
                        'selected_promo' => null,
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

    private function buildAnalytics(array $verifiedUsageCounts = []): array
    {
        $today = today()->toDateString();
        $promoQuery = PromoCode::query();
        $verifiedUsageCounts = $verifiedUsageCounts ?: $this->buildVerifiedUsageCounts();

        $totalPromoCodes = (clone $promoQuery)->count();
        $activePromoCodes = (clone $promoQuery)->where('is_active', true)->count();
        $inactivePromoCodes = (clone $promoQuery)->where('is_active', false)->count();
        $expiredPromoCodes = (clone $promoQuery)->whereDate('end_date', '<', $today)->count();
        $usageLimitReached = (clone $promoQuery)
            ->whereNotNull('usage_limit')
            ->get(['id', 'usage_limit'])
            ->filter(function (PromoCode $promo) use ($verifiedUsageCounts) {
                return (int) ($verifiedUsageCounts[$promo->id] ?? 0) >= (int) $promo->usage_limit;
            })
            ->count();
        $expiringSoon = (clone $promoQuery)
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', today()->addDays(7)->toDateString())
            ->count();
        $totalUses = array_sum($verifiedUsageCounts);
        $totalUsageLimit = (clone $promoQuery)
            ->whereNotNull('usage_limit')
            ->sum('usage_limit');
        $remainingUses = (clone $promoQuery)
            ->whereNotNull('usage_limit')
            ->get(['id', 'usage_limit'])
            ->sum(fn (PromoCode $promo) => max(0, (int) $promo->usage_limit - (int) ($verifiedUsageCounts[$promo->id] ?? 0)));

        $topPromo = (clone $promoQuery)
            ->get(['id', 'code', 'name', 'usage_limit', 'is_active', 'updated_at'])
            ->sort(function (PromoCode $a, PromoCode $b) use ($verifiedUsageCounts) {
                $aCount = (int) ($verifiedUsageCounts[$a->id] ?? 0);
                $bCount = (int) ($verifiedUsageCounts[$b->id] ?? 0);

                if ($aCount === $bCount) {
                    return $b->updated_at <=> $a->updated_at;
                }

                return $bCount <=> $aCount;
            })
            ->first();

        return [
            'total_promo_codes' => $totalPromoCodes,
            'active_promo_codes' => $activePromoCodes,
            'inactive_promo_codes' => $inactivePromoCodes,
            'expired_promo_codes' => $expiredPromoCodes,
            'usage_limit_reached' => $usageLimitReached,
            'expiring_soon' => $expiringSoon,
            'total_uses' => $totalUses,
            'total_usage_limit' => $totalUsageLimit,
            'remaining_uses' => $remainingUses,
            'top_promo' => $topPromo ? [
                'id' => $topPromo->id,
                'code' => $topPromo->code,
                'name' => $topPromo->name,
                'usage_count' => (int) ($verifiedUsageCounts[$topPromo->id] ?? 0),
                'usage_limit' => $topPromo->usage_limit,
                'is_active' => (bool) $topPromo->is_active,
                'remaining_uses' => $topPromo->usage_limit !== null
                    ? max(0, (int) $topPromo->usage_limit - (int) ($verifiedUsageCounts[$topPromo->id] ?? 0))
                    : null,
            ] : null,
        ];
    }

    private function buildVerifiedUsageCounts(?array $promoIds = null): array
    {
        $query = PromoCodeUsage::query()
            ->selectRaw('promo_code_id, COUNT(*) as usage_count')
            ->groupBy('promo_code_id');

        if (!empty($promoIds)) {
            $query->whereIn('promo_code_id', $promoIds);
        }

        return $query
            ->pluck('usage_count', 'promo_code_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    private function buildRecentUsages(?int $promoId = null, ?string $usageFrom = null, ?string $usageTo = null): array
    {
        $query = PromoCodeUsage::query()
            ->with([
                'promoCode:id,code,name',
                'booking:id,transaction_id,customer_name,payment_status,status,total_amount,created_at',
            ]);

        if ($promoId) {
            $query->where('promo_code_id', $promoId);
        }

        if (!empty($usageFrom)) {
            $query->whereDate('created_at', '>=', $usageFrom);
        }

        if (!empty($usageTo)) {
            $query->whereDate('created_at', '<=', $usageTo);
        }

        return $query
            ->orderByDesc('created_at')
            ->limit($promoId ? 50 : 20)
            ->get()
            ->map(function (PromoCodeUsage $usage) {
                return [
                    'id' => $usage->id,
                    'promo_code' => $usage->promoCode?->code,
                    'promo_name' => $usage->promoCode?->name,
                    'booking_id' => $usage->booking_id,
                    'booking_transaction_id' => $usage->booking_transaction_id,
                    'checkout_id' => $usage->checkout_id,
                    'payment_reference' => $usage->payment_reference,
                    'discount_amount' => (float) ($usage->discount_amount ?? 0),
                    'created_at' => $usage->created_at?->toDateTimeString(),
                    'booking_url' => $usage->booking_id ? route('admin.bookings.show', $usage->booking_id) : null,
                    'booking' => $usage->booking ? [
                        'transaction_id' => $usage->booking->transaction_id,
                        'customer_name' => $usage->booking->customer_name,
                        'customer_email' => $usage->booking->customer_email,
                        'customer_phone' => $usage->booking->customer_phone,
                        'payment_status' => $usage->booking->payment_status,
                        'status' => $usage->booking->status,
                        'booking_type' => $usage->booking->booking_type,
                        'checkin_date' => optional($usage->booking->checkin_date)->toDateString(),
                        'checkout_date' => optional($usage->booking->checkout_date)->toDateString(),
                        'total_amount' => $usage->booking->total_amount,
                        'created_at' => $usage->booking->created_at?->toDateTimeString(),
                    ] : null,
                ];
            })
            ->all();
    }

    private function emptyAnalytics(): array
    {
        return [
            'total_promo_codes' => 0,
            'active_promo_codes' => 0,
            'inactive_promo_codes' => 0,
            'expired_promo_codes' => 0,
            'usage_limit_reached' => 0,
            'expiring_soon' => 0,
            'total_uses' => 0,
            'total_usage_limit' => 0,
            'remaining_uses' => 0,
            'top_promo' => null,
        ];
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
            PromoCode::syncAutomaticStatus();

            $promoCode = PromoCode::whereRaw('UPPER(code) = ?', [$code])->first();

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

            $today = today();
            if ($today->lt($promoCode->start_date) || $today->gt($promoCode->end_date)) {
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

            $verifiedUsageCount = $promoCode->verifiedUsageCount();
            if ($promoCode->usage_limit && $verifiedUsageCount >= $promoCode->usage_limit) {
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
