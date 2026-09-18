<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use App\Models\PackageBooking;
use App\Models\DayTour;
use App\Models\Product;
use App\Models\Food;
use App\Models\Staycation;
use App\Models\AdminSystemAlert;
use App\Models\AdminActivityLog;
use App\Services\PaymentService;
use App\Services\PaymongoWebhookService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $this->reconcileRecentPendingPaymongoPayments();

        [$start, $end, $rangeLabel, $range] = $this->resolveDateRange($request);
        $defaultChartView = $this->chartViewFromRange($range, 'daily');
        $salesView = $this->normalizeSalesView($request->query('sales_view', $defaultChartView));
        $bookingView = $this->normalizeSalesView($request->query('booking_view', $defaultChartView));
        $pendingWindowMinutes = (int) config('booking.payment_attempt_expiry_minutes', 1440);
        $pendingCutoff = now()->subMinutes($pendingWindowMinutes);
        $pendingWindowLabel = $pendingWindowMinutes >= 1440
            ? ((int) ceil($pendingWindowMinutes / 1440)) . '-day'
            : $pendingWindowMinutes . '-minute';
        $applyRange = function (Builder $query, string $dateColumn) use ($start, $end) {
            return $query->when(
                $start && $end,
                fn ($q) => $q->whereBetween($dateColumn, [$start, $end])
            );
        };
        $applyRecentRange = function (Builder $query) use ($start, $end) {
            return $query->when(
                $start && $end,
                fn ($q) => $q->whereBetween('created_at', [$start, $end])
            );
        };
        // -----------------------------
        // USERS (1 query)
        // -----------------------------
        $usersAgg = User::query()->selectRaw("
            COUNT(*) as total_users,
            SUM(CASE WHEN email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as verified_users,
            SUM(CASE WHEN user_type = 'admin' THEN 1 ELSE 0 END) as admin_users,
            SUM(CASE WHEN user_type = 'client' THEN 1 ELSE 0 END) as client_users
        ")->first();

        $totalUsers    = (int) $usersAgg->total_users;
        $verifiedUsers = (int) $usersAgg->verified_users;
        $adminUsers    = (int) $usersAgg->admin_users;
        $clientUsers   = (int) $usersAgg->client_users;

        // -----------------------------
        // BOOKINGS + REVENUE (2 queries: Booking + PackageBooking)
        // -----------------------------
        $base = $this->applyServiceDateRange(
            Booking::query()->whereNotIn('payment_status', ['pending', 'failed']),
            'checkin_date',
            $start,
            $end
        );
        $packageBase = $this->applyServiceDateRange(
            PackageBooking::query()->whereNotIn('payment_status', ['pending', 'failed']),
            'booking_date',
            $start,
            $end
        );
        $pendingBase = Booking::query()
            ->where('payment_status', 'pending')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where('created_at', '>=', $pendingCutoff);
        $pendingPackageBase = PackageBooking::query()
            ->where('payment_status', 'pending')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where('created_at', '>=', $pendingCutoff);
        $closedAttemptBase = $applyRange(
            Booking::query()->where(function (Builder $query) use ($pendingCutoff) {
                $query->where('payment_status', 'failed')
                    ->orWhere(function (Builder $stalePending) use ($pendingCutoff) {
                        $stalePending->where('payment_status', 'pending')
                            ->whereNotIn('status', ['cancelled', 'completed'])
                            ->where('created_at', '<', $pendingCutoff);
                    });
            }),
            'created_at'
        );
        $closedAttemptPackageBase = $applyRange(
            PackageBooking::query()->where(function (Builder $query) use ($pendingCutoff) {
                $query->where('payment_status', 'failed')
                    ->orWhere(function (Builder $stalePending) use ($pendingCutoff) {
                        $stalePending->where('payment_status', 'pending')
                            ->whereNotIn('status', ['cancelled', 'completed'])
                            ->where('created_at', '<', $pendingCutoff);
                    });
            }),
            'created_at'
        );
        $today = now()->toDateString();
        $upcomingBookings = Booking::query()
            ->where('status', 'confirmed')
            ->where('payment_status', 'paid')
            ->whereDate('checkin_date', '>', $today)
            ->count()
            + PackageBooking::query()
                ->where('status', 'confirmed')
                ->where('payment_status', 'paid')
                ->whereDate('booking_date', '>', $today)
                ->count();

        $agg = (clone $base)->selectRaw("
            COUNT(*) as total_bookings,
            SUM(CASE WHEN status = 'confirmed' AND payment_status = 'paid' THEN 1 ELSE 0 END) as active_bookings,
            SUM(CASE WHEN status = 'completed' AND payment_status = 'paid' THEN 1 ELSE 0 END) as completed_bookings,
            SUM(CASE WHEN payment_status = 'refunded' THEN 1 ELSE 0 END) as refunded_bookings,

            SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = 'completed' AND payment_status = 'paid' THEN total_amount ELSE 0 END) as completed_revenue,
            SUM(CASE WHEN payment_status = 'refunded' THEN total_amount ELSE 0 END) as refunded_revenue
        ")->first();
        $packageAgg = (clone $packageBase)->selectRaw("
            COUNT(*) as total_bookings,
            SUM(CASE WHEN status = 'confirmed' AND payment_status = 'paid' THEN 1 ELSE 0 END) as active_bookings,
            SUM(CASE WHEN status = 'completed' AND payment_status = 'paid' THEN 1 ELSE 0 END) as completed_bookings,
            SUM(CASE WHEN payment_status = 'refunded' THEN 1 ELSE 0 END) as refunded_bookings,

            SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = 'completed' AND payment_status = 'paid' THEN total_amount ELSE 0 END) as completed_revenue,
            SUM(CASE WHEN payment_status = 'refunded' THEN total_amount ELSE 0 END) as refunded_revenue
        ")->first();
        $pendingAgg = (clone $pendingBase)->selectRaw("
            COUNT(*) as pending_bookings,
            SUM(total_amount) as pending_revenue
        ")->first();
        $pendingPackageAgg = (clone $pendingPackageBase)->selectRaw("
            COUNT(*) as pending_bookings,
            SUM(total_amount) as pending_revenue
        ")->first();
        $totalBookings     = (int) $agg->total_bookings + (int) $packageAgg->total_bookings;
        $activeBookings    = (int) $agg->active_bookings + (int) $packageAgg->active_bookings;
        $completedBookings = (int) $agg->completed_bookings + (int) $packageAgg->completed_bookings;

        $totalRevenue      = (float) $agg->total_revenue + (float) $packageAgg->total_revenue;
        $pendingRevenue    = (float) $pendingAgg->pending_revenue + (float) $pendingPackageAgg->pending_revenue;
        $pendingBookings   = (int) $pendingAgg->pending_bookings + (int) $pendingPackageAgg->pending_bookings;
        $closedAttemptBookings = (clone $closedAttemptBase)->count()
            + (clone $closedAttemptPackageBase)->count();
        $completedRevenue  = (float) $agg->completed_revenue + (float) $packageAgg->completed_revenue;
        $refundedRevenue   = (float) $agg->refunded_revenue + (float) $packageAgg->refunded_revenue;
        $refundedBookings  = (int) $agg->refunded_bookings + (int) $packageAgg->refunded_bookings;
        $paidRevenue       = $totalRevenue;
        $grossRevenue      = $paidRevenue + $refundedRevenue;
        $netRevenue        = $paidRevenue;
        $periodDays = max(1, (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1);
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->subDays($periodDays - 1)->startOfDay();
        $previousPaidRevenue = $this->getPaidRevenueForRange($previousStart, $previousEnd);
        $netRevenueChangePercent = $previousPaidRevenue > 0
            ? (($paidRevenue - $previousPaidRevenue) / $previousPaidRevenue) * 100
            : ($paidRevenue > 0 ? 100 : 0);
        $netRevenueComparisonLabel = $periodDays === 1 ? 'vs previous day' : "vs previous {$periodDays} days";

        // -----------------------------
        // INVENTORY (2 queries: Products + Foods)
        // -----------------------------
        $productsAgg = Product::query()->selectRaw("
            COUNT(*) as total_products,
            SUM(CASE WHEN is_active = 1 AND is_inventory_managed = 1 AND stock_quantity <= 10 THEN 1 ELSE 0 END) as low_stock_products
        ")->first();

        $foodsAgg = Food::query()->selectRaw("
            COUNT(*) as total_foods,
            SUM(CASE WHEN is_active = 1 AND is_inventory_managed = 1 AND stock_quantity <= 10 THEN 1 ELSE 0 END) as low_stock_foods
        ")->first();

        $totalProducts    = (int) $productsAgg->total_products;
        $lowStockProducts = (int) $productsAgg->low_stock_products;

        $totalFoods    = (int) $foodsAgg->total_foods;
        $lowStockFoods = (int) $foodsAgg->low_stock_foods;

        $todayCheckInsBooking = Booking::query()
            ->whereDate('checkin_date', $today)
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->count();

        $todayCheckInsPackage = PackageBooking::query()
            ->whereDate('booking_date', $today)
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->count();

        $todayCheckIns = $todayCheckInsBooking + $todayCheckInsPackage;

        // -----------------------------
        // RECENT BOOKINGS (all statuses, with room/package info)
        // -----------------------------
        $recentBookings = $applyRecentRange(Booking::query())
            ->latest()
            ->take(12)
            ->get()
            ->map(function ($booking) {
                $booking->display_customer_name = $booking->display_customer_name;
                return $booking;
            })
            ->concat(
                $applyRecentRange(PackageBooking::query())
                    ->with('package')
                    ->latest()
                    ->take(12)
                    ->get()
                    ->map(function ($booking) {
                        $booking->display_customer_name = $booking->full_name;
                        $booking->booking_type = 'package';
                        $booking->checkin_date = $booking->booking_date;
                        return $booking;
                    })
            )
            ->sortByDesc('created_at')
            ->take(6)
            ->values();
        $recentStaycationIds = $recentBookings
            ->filter(fn ($booking) => $booking instanceof Booking)
            ->flatMap(function (Booking $booking) {
                return collect(data_get($booking->booking_data, 'accommodations', []))
                    ->map(fn ($accommodation) => is_array($accommodation) ? ($accommodation['room_id'] ?? $accommodation['id'] ?? null) : null)
                    ->filter();
            })
            ->unique()
            ->values();
        $recentDayTourIds = $recentBookings
            ->filter(fn ($booking) => $booking instanceof Booking)
            ->flatMap(function (Booking $booking) {
                $bookingData = $booking->booking_data ?? [];
                $ids = collect([
                    $bookingData['package_id'] ?? null,
                    data_get($bookingData, 'packages.0.package_id'),
                    data_get($bookingData, 'packages.0.daytour_id'),
                    data_get($bookingData, 'packages.0.id'),
                ]);

                return $ids->filter();
            })
            ->unique()
            ->values();
        $recentStaycations = $recentStaycationIds->isNotEmpty()
            ? Staycation::query()->with('swiperImages')->whereIn('id', $recentStaycationIds)->get()->keyBy('id')
            : collect();
        $recentDayTours = $recentDayTourIds->isNotEmpty()
            ? DayTour::query()->with('swiperImages')->whereIn('id', $recentDayTourIds)->get()->keyBy('id')
            : collect();
        $recentBookings = $recentBookings
            ->map(function ($booking) use ($recentStaycations, $recentDayTours) {
                $booking->setAttribute('recent_booking_item', $this->resolveRecentBookingItemName($booking, $recentStaycations, $recentDayTours));
                $booking->setAttribute('recent_booking_image', $this->resolveRecentBookingImage($booking, $recentStaycations, $recentDayTours));
                $booking->setAttribute('recent_booking_meta', $this->resolveRecentBookingMeta($booking));

                return $booking;
            })
            ->values();

        // -----------------------------
        // MONTHLY BOOKINGS (2 queries merged)
        // (last 12 months, independent of range filter)
        // -----------------------------
        $bookingMonthly = $this->getMonthlyBookingCounts(
            Booking::query()->whereNotIn('payment_status', ['pending', 'failed'])
        );
        $packageMonthly = $this->getMonthlyBookingCounts(
            PackageBooking::query()->whereNotIn('payment_status', ['pending', 'failed'])
        );
        $monthlyBookings = $bookingMonthly
            ->concat($packageMonthly)
            ->groupBy(fn($row) => $row->year . '-' . $row->month)
            ->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'year' => (int) $first->year,
                    'month' => (int) $first->month,
                    'count' => (int) $group->sum('count'),
                ];
            })
            ->sortBy(fn($row) => sprintf('%04d-%02d', $row->year, $row->month))
            ->values();

        $bookingOverviewSeries = $this->buildBookingOverviewSeries($start, $end, $bookingView);
        $revenueSummarySeries = $this->buildRevenueSummarySeries($start, $end);
        $bookingOverviewChartItems = $bookingOverviewSeries->values();
        $salesReport = $this->buildSalesReport($start, $end, $salesView, $pendingRevenue);
        $salesReportCards = $salesReport['cards'];
        $salesReportSeries = $salesReport['series'];
        $salesDistributionSeries = $salesReport['distribution'];
        $salesPendingRevenue = $salesReport['pendingRevenue'];
        $salesTotalTransactions = $salesReport['totalTransactions'];
        $salesTotalAmount = $salesReport['totalAmount'];
        $salesTrendLabel = $salesReport['trendLabel'];
        $salesModeOptions = $salesReport['modeOptions'];
        $salesCategoryCards = $salesReport['categoryCards'];
        $salesTrendChartSeries = $salesReport['trendChart'];

        // -----------------------------------------------
        // SYSTEM ALERTS (Unresolved Critical + Warnings)
        // -----------------------------------------------
        $systemAlerts = AdminSystemAlert::unresolved()
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // -----------------------------------------------
        // RECENT ADMIN ACTIVITY
        // -----------------------------------------------
        $recentActivity = AdminActivityLog::with('user')
            ->latest()
            ->limit(8)
            ->get();

        $viewData = compact(
            'rangeLabel', 'start', 'end', 'range',
            'clientUsers', 'totalUsers', 'verifiedUsers', 'adminUsers',
            'totalBookings', 'activeBookings', 'completedBookings', 'upcomingBookings',
            'netRevenue', 'grossRevenue', 'paidRevenue', 'totalRevenue', 'pendingRevenue', 'pendingBookings', 'closedAttemptBookings', 'pendingWindowMinutes', 'completedRevenue', 'refundedRevenue', 'refundedBookings', 'todayCheckIns',
            'netRevenueChangePercent', 'netRevenueComparisonLabel',
            'totalProducts', 'totalFoods', 'lowStockProducts', 'lowStockFoods',
            'recentBookings', 'monthlyBookings', 'bookingOverviewSeries', 'revenueSummarySeries', 'bookingOverviewChartItems', 'salesView',
            'salesReportCards', 'salesReportSeries', 'salesDistributionSeries', 'salesPendingRevenue', 'salesTotalTransactions',
            'salesTotalAmount', 'salesTrendLabel', 'salesModeOptions',
            'salesCategoryCards', 'salesTrendChartSeries', 'bookingView',
            'systemAlerts', 'recentActivity'
        );

        return view('admin.dashboard', array_merge(
            $viewData,
            $this->buildDashboardViewData($request, $viewData)
        ));
    }

    private function reconcileRecentPendingPaymongoPayments(): void
    {
        $paymentService = app(PaymentService::class);

        Booking::query()
            ->where('payment_method', 'paymongo')
            ->where('payment_status', 'pending')
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNotNull('paymongo_checkout_id')
            ->latest('id')
            ->limit(25)
            ->get()
            ->each(function (Booking $booking) use ($paymentService) {
                $paymentStatus = $paymentService->getCheckoutPaymentStatus($booking->paymongo_checkout_id);

                if (($paymentStatus['status'] ?? null) !== 'paid') {
                    return;
                }

                $paymentService->confirmPendingBooking($booking, [
                    'checkout_id' => $booking->paymongo_checkout_id,
                    'payment_reference' => $paymentStatus['payment_reference'] ?? null,
                    'paid_amount_centavos' => $paymentStatus['paid_amount_centavos'] ?? null,
                ]);
            });

        PackageBooking::query()
            ->where('payment_status', 'pending')
            ->where('status', 'pending_payment')
            ->whereNotNull('paymongo_reference')
            ->latest('id')
            ->limit(25)
            ->get()
            ->each(function (PackageBooking $booking) use ($paymentService) {
                $paymentStatus = $paymentService->getCheckoutPaymentStatus($booking->paymongo_reference);

                if (($paymentStatus['status'] ?? null) !== 'paid') {
                    return;
                }

                PaymongoWebhookService::markPackageBookingPaid(
                    $booking,
                    $paymentStatus['payment_reference'] ?? $booking->paymongo_reference,
                    $paymentStatus['paid_amount_centavos'] ?? null
                );
            });
    }

    private function buildDashboardViewData(Request $request, array $data): array
    {
        extract($data, EXTR_SKIP);

        $peso = html_entity_decode('&#8369;');
        $fmtMoney = fn ($value) => $peso . number_format((float) $value, 2);
        $fmtCompactMoney = function ($value) use ($peso) {
            $value = (float) $value;

            if ($value >= 1000000) {
                return $peso . rtrim(rtrim(number_format($value / 1000000, 2), '0'), '.') . 'M';
            }

            if ($value >= 1000) {
                return $peso . rtrim(rtrim(number_format($value / 1000, 2), '0'), '.') . 'K';
            }

            return $peso . number_format($value, 0);
        };
        $fmtInt = fn ($value) => number_format((int) $value);
        $initials = function (?string $name) {
            $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
            $letters = collect($parts)
                ->filter()
                ->take(2)
                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                ->implode('');

            return $letters !== '' ? $letters : 'A';
        };

        $from = $request->query('from');
        $to = $request->query('to');
        $dateRangeParams = [];

        if (!empty($start) && !empty($end)) {
            $dateRangeParams = [
                'date_from' => optional($start)->toDateString(),
                'date_to' => optional($end)->toDateString(),
            ];
        }

        $rangeShortLabel = match ($range) {
            'today' => 'Today',
            '7d' => '7 Days',
            '30d' => '30 Days',
            'custom' => 'Custom',
            default => '30 Days',
        };

        $rangeDisplay = $start && $end
            ? ($start->isSameDay($end)
                ? $start->format('M j, Y')
                : $start->format('M j') . ' - ' . $end->format('M j, Y'))
            : $rangeShortLabel;

        $revenueChangeUp = $netRevenueChangePercent >= 0;
        $revenueChangeLabel = ($revenueChangeUp ? '+' : '') . number_format($netRevenueChangePercent, 1) . '%';
        $bookingOverviewUpcomingTotal = max(0, (int) $bookingOverviewChartItems->sum('upcoming'));
        $bookingOverviewCompletedTotal = max(0, (int) $bookingOverviewChartItems->sum('completed'));
        $bookingOverviewActiveTotal = max(0, (int) $bookingOverviewChartItems->sum('active'));
        $bookingOverviewCancelledTotal = max(0, (int) $bookingOverviewChartItems->sum('cancelled'));
        $bookingDistributionCounts = [
            'upcoming' => $bookingOverviewUpcomingTotal,
            'completed' => $bookingOverviewCompletedTotal,
            'active' => $bookingOverviewActiveTotal,
            'cancelled' => $bookingOverviewCancelledTotal,
        ];
        $bookingSummaryTotal = max(0, (int) array_sum($bookingDistributionCounts));

        $overviewCards = [
            [
                'label' => 'Upcoming',
                'value' => $fmtInt($upcomingBookings),
                'description' => 'All future paid reservations',
                'icon' => 'calendar-days',
                'iconWrap' => 'bg-blue-50 text-blue-600',
                'linkTone' => 'text-blue-600',
                'href' => route('admin.bookings.index', [
                    'date_from' => now()->addDay()->toDateString(),
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                ]),
            ],
            [
                'label' => 'Completed',
                'value' => $fmtInt($completedBookings),
                'description' => 'Paid check-outs in selected range',
                'icon' => 'badge-check',
                'iconWrap' => 'bg-emerald-50 text-emerald-600',
                'linkTone' => 'text-emerald-600',
                'href' => route('admin.bookings.index', array_merge($dateRangeParams, [
                    'status' => 'completed',
                    'payment_status' => 'paid',
                ])),
            ],
            [
                'label' => 'Live Pending',
                'value' => $fmtInt($pendingBookings),
                'description' => 'Current 24-hour snapshot',
                'icon' => 'clock-3',
                'iconWrap' => 'bg-orange-50 text-orange-500',
                'linkTone' => 'text-orange-500',
                'href' => route('admin.bookings.index', ['payment_scope' => 'attempts', 'payment_status' => 'pending']),
            ],
            [
                'label' => 'Closed Attempts',
                'value' => $fmtInt($closedAttemptBookings),
                'description' => 'Failed or stale in selected range',
                'icon' => 'x-circle',
                'iconWrap' => 'bg-rose-50 text-rose-600',
                'linkTone' => 'text-rose-600',
                'href' => route('admin.bookings.index', array_merge($dateRangeParams, ['payment_scope' => 'attempts', 'payment_status' => 'failed'])),
            ],
            [
                'label' => 'Today',
                'value' => $fmtInt($todayCheckIns),
                'description' => 'Paid reservations today',
                'icon' => 'users',
                'iconWrap' => 'bg-violet-50 text-violet-600',
                'linkTone' => 'text-violet-600',
                'href' => route('admin.bookings.index', [
                    'date_from' => now()->toDateString(),
                    'date_to' => now()->toDateString(),
                    'payment_status' => 'paid',
                    'exclude_cancelled' => 1,
                ]),
            ],
        ];

        $revenueTiles = [
            [
                'label' => 'Net Booking Revenue',
                'value' => $fmtMoney($netRevenue),
                'caption' => $netRevenueComparisonLabel,
                'meta' => 'Paid booking value after recorded refunds',
                'accent' => 'text-slate-900',
            ],
            [
                'label' => 'Live Pending Value',
                'value' => $fmtMoney($pendingRevenue),
                'caption' => 'Potential booking value',
                'meta' => 'Active payment attempts within 24 hours',
                'accent' => 'text-orange-500',
            ],
            [
                'label' => 'Refund Amount',
                'value' => $fmtMoney($refundedRevenue),
                'caption' => 'Returned bookings',
                'meta' => 'Refunded transactions',
                'accent' => 'text-violet-600',
            ],
            [
                'label' => 'Gross Booking Revenue',
                'value' => $fmtMoney($grossRevenue),
                'caption' => 'Before refunds',
                'meta' => 'Net paid revenue plus refunded amount',
                'accent' => 'text-slate-900',
            ],
        ];

        $salesViewOptions = collect($salesModeOptions ?? [])->map(function ($label, $value) use ($request) {
            $rangeValue = match ($value) {
                'daily' => 'today',
                'weekly' => '7d',
                'monthly' => '30d',
                default => 'today',
            };

            return [
                'label' => $label,
                'value' => $value,
                'range' => $rangeValue,
                'url' => $request->fullUrlWithQuery(['range' => $rangeValue]),
            ];
        })->values();
        $bookingViewOptions = collect($salesModeOptions ?? [])->map(function ($label, $value) use ($request) {
            return [
                'label' => $label,
                'value' => $value,
                'url' => $request->fullUrlWithQuery(['booking_view' => $value]),
            ];
        })->values();

        $salesDistributionItems = collect($salesDistributionSeries ?? [])->map(function ($item) {
            return (object) [
                'label' => $item->label,
                'amount' => (float) $item->amount,
                'count' => (int) $item->count,
                'percent' => (float) $item->percent,
                'color' => $item->color,
            ];
        })->values();
        $salesDistributionTotal = max(1, (float) $salesDistributionItems->sum('amount'));
        $salesDistributionActualTotal = (float) $salesDistributionItems->sum('amount');
        $salesDistributionRadius = 72;
        $salesDistributionCircumference = 2 * pi() * $salesDistributionRadius;

        $salesCategoryCards = collect($salesCategoryCards ?? []);
        $salesTrendChartSeries = collect($salesTrendChartSeries ?? []);
        $salesTrendChartPayload = [
            'labels' => $salesTrendChartSeries->pluck('label')->values()->all(),
            'datasets' => [
                [
                    'label' => 'Staycation',
                    'data' => $salesTrendChartSeries->pluck('staycation')->map(fn ($value) => (float) $value)->values()->all(),
                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#3B82F6',
                    'stack' => 'sales',
                ],
                [
                    'label' => 'Food & Beverage',
                    'data' => $salesTrendChartSeries->pluck('food')->map(fn ($value) => (float) $value)->values()->all(),
                    'backgroundColor' => '#10B981',
                    'borderColor' => '#10B981',
                    'stack' => 'sales',
                ],
                [
                    'label' => 'Activities',
                    'data' => $salesTrendChartSeries->pluck('activities')->map(fn ($value) => (float) $value)->values()->all(),
                    'backgroundColor' => '#F59E0B',
                    'borderColor' => '#F59E0B',
                    'stack' => 'sales',
                ],
                [
                    'label' => 'Refunds',
                    'data' => $salesTrendChartSeries->pluck('refunds')->map(fn ($value) => -abs((float) $value))->values()->all(),
                    'backgroundColor' => '#8B5CF6',
                    'borderColor' => '#8B5CF6',
                    'stack' => 'sales',
                ],
            ],
        ];

        $revenueSummarySeries = collect($revenueSummarySeries ?? []);
        $revenueChartCount = max(1, $revenueSummarySeries->count());
        $revenueChartMax = max(1, (float) $revenueSummarySeries->map(fn ($row) => (float) ($row->net ?? 0))->max());
        $revenueChartWidth = max(980, 84 + ($revenueChartCount * 42));
        $revenueChartHeight = 300;
        $revenueChartLeft = 64;
        $revenueChartRight = 24;
        $revenueChartTop = 24;
        $revenueChartBottom = 56;
        $revenueChartPlotHeight = $revenueChartHeight - $revenueChartTop - $revenueChartBottom;
        $revenueLabelStep = match (true) {
            $revenueChartCount > 28 => 4,
            $revenueChartCount > 18 => 3,
            $revenueChartCount > 12 => 2,
            default => 1,
        };
        $revenueChartLine = function ($key) use ($revenueSummarySeries, $revenueChartCount, $revenueChartMax, $revenueChartWidth, $revenueChartHeight, $revenueChartLeft, $revenueChartRight, $revenueChartBottom, $revenueChartPlotHeight) {
            $points = [];

            foreach ($revenueSummarySeries as $index => $row) {
                $value = (float) ($row->{$key} ?? 0);
                $x = $revenueChartCount === 1
                    ? ($revenueChartWidth / 2)
                    : $revenueChartLeft + (($revenueChartWidth - $revenueChartLeft - $revenueChartRight) * ($index / ($revenueChartCount - 1)));
                $y = $revenueChartHeight - $revenueChartBottom - ($revenueChartPlotHeight * ($value / $revenueChartMax));
                $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
            }

            return implode(' ', $points);
        };
        $revenueChartArea = function ($key) use ($revenueSummarySeries, $revenueChartCount, $revenueChartMax, $revenueChartWidth, $revenueChartHeight, $revenueChartLeft, $revenueChartRight, $revenueChartBottom, $revenueChartPlotHeight) {
            $points = [];

            foreach ($revenueSummarySeries as $index => $row) {
                $value = (float) ($row->{$key} ?? 0);
                $x = $revenueChartCount === 1
                    ? ($revenueChartWidth / 2)
                    : $revenueChartLeft + (($revenueChartWidth - $revenueChartLeft - $revenueChartRight) * ($index / ($revenueChartCount - 1)));
                $y = $revenueChartHeight - $revenueChartBottom - ($revenueChartPlotHeight * ($value / $revenueChartMax));
                $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
            }

            $startX = $revenueChartCount === 1
                ? ($revenueChartWidth / 2)
                : $revenueChartLeft;
            $endX = $revenueChartCount === 1
                ? ($revenueChartWidth / 2)
                : ($revenueChartWidth - $revenueChartRight);
            $baseY = $revenueChartHeight - $revenueChartBottom;

            array_unshift($points, number_format($startX, 2, '.', '') . ',' . number_format($baseY, 2, '.', ''));
            $points[] = number_format($endX, 2, '.', '') . ',' . number_format($baseY, 2, '.', '');

            return implode(' ', $points);
        };
        $revenueTrendExportPayload = [
            'labels' => $revenueSummarySeries->pluck('label')->values()->all(),
            'datasets' => [
                [
                    'label' => 'Net Booking Revenue',
                    'data' => $revenueSummarySeries->pluck('net')->map(fn ($value) => (float) $value)->values()->all(),
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 5,
                    'borderWidth' => 3,
                ],
            ],
        ];

        $bookingOverviewChartItems = collect($bookingOverviewChartItems ?? []);
        $bookingOverviewCount = max(1, $bookingOverviewChartItems->count());
        $bookingOverviewMax = max(1, (int) $bookingOverviewChartItems->map(fn ($row) => max(
            (int) ($row->upcoming ?? 0),
            (int) ($row->completed ?? 0),
            (int) ($row->active ?? 0),
            (int) ($row->cancelled ?? 0)
        ))->max());
        $bookingTickStep = max(1, (int) ceil($bookingOverviewMax / 4));
        $bookingAxisMax = $bookingTickStep * 4;
        $bookingChartWidth = 1120;
        $bookingChartHeight = 320;
        $bookingChartLeft = 56;
        $bookingChartRight = 28;
        $bookingChartTop = 28;
        $bookingChartBottom = 42;
        $bookingChartPlotHeight = $bookingChartHeight - $bookingChartTop - $bookingChartBottom;
        $bookingLabelStep = match ($bookingView ?? 'daily') {
            'daily' => $bookingOverviewCount > 20 ? 2 : 1,
            'weekly', 'monthly' => 1,
            default => max(1, (int) ceil($bookingOverviewCount / 8)),
        };
        $bookingChartLine = function ($key) use ($bookingOverviewChartItems, $bookingOverviewCount, $bookingAxisMax, $bookingChartWidth, $bookingChartHeight, $bookingChartLeft, $bookingChartRight, $bookingChartBottom, $bookingChartPlotHeight) {
            $points = [];

            foreach ($bookingOverviewChartItems as $index => $row) {
                $value = (int) ($row->{$key} ?? 0);
                $x = $bookingOverviewCount === 1
                    ? ($bookingChartWidth / 2)
                    : $bookingChartLeft + (($bookingChartWidth - $bookingChartLeft - $bookingChartRight) * ($index / ($bookingOverviewCount - 1)));
                $y = $bookingChartHeight - $bookingChartBottom - ($bookingChartPlotHeight * ($value / $bookingAxisMax));
                $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
            }

            return implode(' ', $points);
        };

        $bookingSeriesMeta = collect([
            ['key' => 'upcoming', 'label' => 'Confirmed (Paid)', 'stroke' => '#2563eb', 'tone' => 'text-blue-600'],
            ['key' => 'completed', 'label' => 'Completed (Paid)', 'stroke' => '#22c55e', 'tone' => 'text-emerald-600'],
            ['key' => 'active', 'label' => 'Live Pending', 'stroke' => '#f97316', 'tone' => 'text-orange-500'],
            ['key' => 'cancelled', 'label' => 'Other / Closed', 'stroke' => '#ef4444', 'tone' => 'text-red-500'],
        ]);
        $bookingSummaryBaseRows = $bookingSeriesMeta
            ->map(function (array $series) use ($bookingDistributionCounts, $bookingSummaryTotal, $fmtInt) {
                $count = $bookingDistributionCounts[$series['key']] ?? 0;
                $rawPercent = $bookingSummaryTotal > 0 ? (($count / $bookingSummaryTotal) * 100) : 0;

                return [
                    'key' => $series['key'],
                    'label' => $series['label'],
                    'count' => $fmtInt($count),
                    'count_value' => $count,
                    'raw_percent' => $rawPercent,
                    'floor_percent' => (int) floor($rawPercent),
                    'remainder' => $rawPercent - floor($rawPercent),
                    'tone' => $series['tone'],
                ];
            });
        $bookingOverviewExportPayload = [
            'labels' => $bookingOverviewChartItems->pluck('label')->values()->all(),
            'datasets' => $bookingSeriesMeta->map(function (array $series) use ($bookingOverviewChartItems, $fmtInt) {
                $color = $series['stroke'];
                $values = $bookingOverviewChartItems->pluck($series['key'])->map(fn ($value) => (int) $value)->values();
                $seriesTotal = (int) $values->sum();

                return [
                    'label' => $series['label'] . ' (' . $fmtInt($seriesTotal) . ')',
                    'data' => $values->all(),
                    'borderColor' => $color,
                    'backgroundColor' => $color,
                    'fill' => false,
                    'tension' => 0.35,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 5,
                    'borderWidth' => 3,
                ];
            })->values()->all(),
        ];
        $bookingPercentRemainder = $bookingSummaryTotal > 0
            ? max(0, 100 - $bookingSummaryBaseRows->sum('floor_percent'))
            : 0;
        $bookingPercentByKey = [];
        $bookingSummaryBaseRows
            ->sortByDesc(fn (array $row) => $row['remainder'])
            ->values()
            ->each(function (array $row, int $index) use (&$bookingPercentByKey, $bookingPercentRemainder) {
                $bookingPercentByKey[$row['key']] = $row['floor_percent'] + ($index < $bookingPercentRemainder ? 1 : 0);
            });
        $bookingSummaryBreakdown = $bookingSummaryBaseRows
            ->map(function (array $row) use ($bookingPercentByKey) {
                return [
                    'label' => $row['label'],
                    'count' => $row['count'],
                    'percent' => $bookingPercentByKey[$row['key']] ?? 0,
                    'tone' => $row['tone'],
                ];
            })->values();

        $recentBookingCards = collect($recentBookings ?? [])->map(function ($booking) use ($fmtMoney, $initials) {
            $isPackage = $booking instanceof PackageBooking;
            $detailHref = $isPackage
                ? route('admin.package_bookings.show', $booking->id)
                : route('admin.bookings.show', $booking->id);
            $paymentStatus = strtolower((string) ($booking->payment_status ?? ''));
            $displayStatus = $paymentStatus === 'refunded'
                ? 'Refunded'
                : ucfirst((string) $booking->status);
            $statusTone = $paymentStatus === 'refunded'
                ? 'bg-purple-50 text-purple-700'
                : match (strtolower((string) $booking->status)) {
                'completed' => 'bg-emerald-50 text-emerald-700',
                'confirmed' => 'bg-blue-50 text-blue-700',
                'cancelled' => 'bg-red-50 text-red-700',
                default => 'bg-orange-50 text-orange-700',
                };

            return [
                'name' => $booking->display_customer_name,
                'initials' => $initials($booking->display_customer_name),
                'item' => $booking->recent_booking_item,
                'meta' => (string) ($booking->recent_booking_meta ?? 'No date'),
                'image' => $booking->recent_booking_image,
                'amount' => $fmtMoney($booking->total_amount),
                'status' => $displayStatus,
                'statusTone' => $statusTone,
                'href' => $detailHref,
            ];
        })->values();

        $activityCards = collect($recentActivity ?? [])->take(5)->map(function ($activity) use ($initials) {
            $actor = data_get($activity, 'user.first_name') ?: 'Admin';
            $description = $activity->description ?: ucfirst(str_replace('_', ' ', (string) $activity->action));

            return [
                'actor' => $actor,
                'initials' => $initials($actor),
                'title' => $description,
                'meta' => ucfirst((string) $activity->action),
                'time' => optional($activity->created_at)->diffForHumans(),
            ];
        })->values();

        $rangeOptions = [
            'today' => 'Today',
            '7d' => 'Last 7 Days',
            '30d' => 'Last 30 Days',
            'custom' => 'Custom Range',
        ];
        $quickActions = [
            [
                'title' => 'Manage Bookings',
                'description' => 'View and manage all bookings',
                'href' => route('admin.bookings.index'),
                'icon' => 'clipboard-check',
                'tone' => 'text-sky-600',
            ],
            [
                'title' => 'Manage Users',
                'description' => 'Add or manage users',
                'href' => route('admin.users.index'),
                'icon' => 'users',
                'tone' => 'text-emerald-600',
            ],
            [
                'title' => 'Manage Inventory',
                'description' => 'Update rooms, items and stock',
                'href' => route('admin.bookings.inventory'),
                'icon' => 'boxes',
                'tone' => 'text-violet-600',
            ],
        ];

        return compact(
            'peso', 'fmtMoney', 'fmtCompactMoney', 'fmtInt',
            'from', 'to', 'dateRangeParams', 'rangeShortLabel', 'rangeDisplay',
            'revenueChangeUp', 'revenueChangeLabel',
            'bookingOverviewUpcomingTotal', 'bookingOverviewCompletedTotal',
            'bookingOverviewActiveTotal', 'bookingOverviewCancelledTotal',
            'bookingDistributionCounts', 'bookingSummaryTotal',
            'overviewCards', 'revenueTiles', 'salesViewOptions', 'bookingViewOptions',
            'salesDistributionItems', 'salesDistributionTotal', 'salesDistributionActualTotal',
            'salesDistributionRadius', 'salesDistributionCircumference',
            'salesCategoryCards', 'salesTrendChartSeries', 'salesTrendChartPayload',
            'revenueSummarySeries', 'revenueChartCount', 'revenueChartMax',
            'revenueChartWidth', 'revenueChartHeight', 'revenueChartLeft',
            'revenueChartRight', 'revenueChartTop', 'revenueChartBottom',
            'revenueChartPlotHeight', 'revenueLabelStep', 'revenueChartLine',
            'revenueChartArea', 'revenueTrendExportPayload',
            'bookingOverviewChartItems', 'bookingOverviewCount', 'bookingOverviewMax',
            'bookingTickStep', 'bookingAxisMax', 'bookingChartWidth', 'bookingChartHeight',
            'bookingChartLeft', 'bookingChartRight', 'bookingChartTop',
            'bookingChartBottom', 'bookingChartPlotHeight', 'bookingLabelStep',
            'bookingChartLine', 'bookingSeriesMeta', 'bookingSummaryBreakdown',
            'bookingOverviewExportPayload', 'recentBookingCards', 'activityCards',
            'rangeOptions', 'quickActions'
        );
    }

    public function export(Request $request)
    {
        [$start, $end, $rangeLabel] = $this->resolveDateRange($request);
        $pendingCutoff = now()->subMinutes(60);
        $usersAgg = User::query()->selectRaw("
            COUNT(*) as total_users,
            SUM(CASE WHEN email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as verified_users,
            SUM(CASE WHEN user_type = 'admin' THEN 1 ELSE 0 END) as admin_users,
            SUM(CASE WHEN user_type = 'client' THEN 1 ELSE 0 END) as client_users
        ")->first();

        $base = $this->applyServiceDateRange(
            Booking::query()->whereNotIn('payment_status', ['pending', 'failed']),
            'checkin_date',
            $start,
            $end
        );
        $packageBase = $this->applyServiceDateRange(
            PackageBooking::query()->whereNotIn('payment_status', ['pending', 'failed']),
            'booking_date',
            $start,
            $end
        );
        $pendingBase = Booking::query()
            ->where('payment_status', 'pending')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where('created_at', '>=', $pendingCutoff);
        $pendingPackageBase = PackageBooking::query()
            ->where('payment_status', 'pending')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where('created_at', '>=', $pendingCutoff);

        $agg = (clone $base)->selectRaw("
            COUNT(*) as total_bookings,
            SUM(CASE WHEN status = 'confirmed' AND payment_status = 'paid' THEN 1 ELSE 0 END) as active_bookings,
            SUM(CASE WHEN status = 'completed' AND payment_status = 'paid' THEN 1 ELSE 0 END) as completed_bookings,
            SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = 'completed' AND payment_status = 'paid' THEN total_amount ELSE 0 END) as completed_revenue,
            SUM(CASE WHEN payment_status = 'refunded' THEN total_amount ELSE 0 END) as refunded_revenue
        ")->first();
        $packageAgg = (clone $packageBase)->selectRaw("
            COUNT(*) as total_bookings,
            SUM(CASE WHEN status = 'confirmed' AND payment_status = 'paid' THEN 1 ELSE 0 END) as active_bookings,
            SUM(CASE WHEN status = 'completed' AND payment_status = 'paid' THEN 1 ELSE 0 END) as completed_bookings,
            SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = 'completed' AND payment_status = 'paid' THEN total_amount ELSE 0 END) as completed_revenue,
            SUM(CASE WHEN payment_status = 'refunded' THEN total_amount ELSE 0 END) as refunded_revenue
        ")->first();
        $pendingAgg = (clone $pendingBase)->selectRaw("
            COUNT(*) as pending_bookings,
            SUM(total_amount) as pending_revenue
        ")->first();
        $pendingPackageAgg = (clone $pendingPackageBase)->selectRaw("
            COUNT(*) as pending_bookings,
            SUM(total_amount) as pending_revenue
        ")->first();

        $productsAgg = Product::query()->selectRaw("
            COUNT(*) as total_products,
            SUM(CASE WHEN is_active = 1 AND is_inventory_managed = 1 AND stock_quantity <= 10 THEN 1 ELSE 0 END) as low_stock_products
        ")->first();
        $foodsAgg = Food::query()->selectRaw("
            COUNT(*) as total_foods,
            SUM(CASE WHEN is_active = 1 AND is_inventory_managed = 1 AND stock_quantity <= 10 THEN 1 ELSE 0 END) as low_stock_foods
        ")->first();

        $totalUsers    = (int) $usersAgg->total_users;
        $verifiedUsers = (int) $usersAgg->verified_users;
        $adminUsers    = (int) $usersAgg->admin_users;
        $clientUsers   = (int) $usersAgg->client_users;

        $totalBookings     = (int) $agg->total_bookings + (int) $packageAgg->total_bookings;
        $activeBookings    = (int) $agg->active_bookings + (int) $packageAgg->active_bookings;
        $completedBookings = (int) $agg->completed_bookings + (int) $packageAgg->completed_bookings;
        $totalRevenue      = (float) $agg->total_revenue + (float) $packageAgg->total_revenue;
        $pendingRevenue    = (float) $pendingAgg->pending_revenue + (float) $pendingPackageAgg->pending_revenue;
        $pendingBookings   = (int) $pendingAgg->pending_bookings + (int) $pendingPackageAgg->pending_bookings;
        $completedRevenue  = (float) $agg->completed_revenue + (float) $packageAgg->completed_revenue;
        $refundedRevenue   = (float) $agg->refunded_revenue + (float) $packageAgg->refunded_revenue;
        $paidRevenue       = $totalRevenue;
        $grossRevenue      = $paidRevenue + $refundedRevenue;
        $netRevenue        = $paidRevenue;
        $totalProducts     = (int) $productsAgg->total_products;
        $lowStockProducts  = (int) $productsAgg->low_stock_products;
        $totalFoods        = (int) $foodsAgg->total_foods;
        $lowStockFoods     = (int) $foodsAgg->low_stock_foods;

        $bookingItems = (clone $base)
            ->latest()
            ->get();
        $packageItems = (clone $packageBase)
            ->latest()
            ->get();
        $mergedItems = $bookingItems
            ->concat($packageItems)
            ->sortByDesc('created_at')
            ->values();

        $bookingRows = $bookingItems
            ->map(function (Booking $booking) {
                return [
                    'Type' => $booking->booking_type,
                    'Customer' => $booking->display_customer_name,
                    'Email' => $booking->customer_email,
                    'Phone' => $booking->customer_phone,
                    'Reference' => $booking->transaction_id,
                    'Check-in' => optional($booking->checkin_date)->format('M d, Y'),
                    'Check-out' => optional($booking->display_checkout_date)->format('M d, Y'),
                    'Status' => $booking->status,
                    'Payment' => $booking->payment_status,
                    'Amount' => (float) $booking->total_amount,
                    'Created At' => optional($booking->created_at)->format('M d, Y H:i'),
                ];
            })
            ->values();

        $packageRows = $packageItems
            ->map(function (PackageBooking $booking) {
                return [
                    'Type' => 'package',
                    'Customer' => $booking->full_name,
                    'Email' => $booking->email,
                    'Phone' => $booking->phone,
                    'Reference' => $booking->booking_reference,
                    'Check-in' => optional($booking->booking_date)->format('M d, Y'),
                    'Check-out' => 'N/A',
                    'Status' => $booking->status,
                    'Payment' => $booking->payment_status,
                    'Amount' => (float) $booking->total_amount,
                    'Created At' => optional($booking->created_at)->format('M d, Y H:i'),
                ];
            })
            ->values();

        $paidBookingCount = $mergedItems
            ->filter(fn ($item) => strtolower((string) ($item->payment_status ?? '')) === 'paid')
            ->count();
        $bookingTypeRows = [
            ['Metric' => 'Staycation Bookings', 'Value' => $bookingItems->whereIn('booking_type', Booking::STAYCATION_TYPES)->count()],
            ['Metric' => 'Package Bookings', 'Value' => $packageItems->count()],
            ['Metric' => 'Day Visit Bookings', 'Value' => $bookingItems->where('booking_type', 'dayvisit')->count()],
            ['Metric' => 'Confirmed Paid', 'Value' => $mergedItems->filter(fn ($item) => $item->status === 'confirmed' && $item->payment_status === 'paid')->count()],
            ['Metric' => 'Completed Paid', 'Value' => $mergedItems->filter(fn ($item) => $item->status === 'completed' && $item->payment_status === 'paid')->count()],
            ['Metric' => 'Cancelled', 'Value' => $mergedItems->where('status', 'cancelled')->count()],
            ['Metric' => 'Paid', 'Value' => $mergedItems->filter(fn ($item) => strtolower((string) ($item->payment_status ?? '')) === 'paid')->count()],
            ['Metric' => 'Pending', 'Value' => $pendingBookings],
        ];

        $revenueAnalyticsRows = [
            ['Metric' => 'Gross Booking Revenue', 'Value' => $grossRevenue],
            ['Metric' => 'Net Booking Revenue', 'Value' => $netRevenue],
            ['Metric' => 'Paid Completed Booking Revenue', 'Value' => $completedRevenue],
            ['Metric' => 'Live Pending Value', 'Value' => $pendingRevenue],
            ['Metric' => 'Refunded Revenue', 'Value' => $refundedRevenue],
            ['Metric' => 'Average Paid Booking Value', 'Value' => $paidBookingCount > 0 ? $netRevenue / $paidBookingCount : 0],
            ['Metric' => 'Paid Booking Count', 'Value' => $paidBookingCount],
            ['Metric' => 'Non-attempt Booking Records', 'Value' => $mergedItems->count()],
        ];

        $monthlyBookings = collect(range(11, 0))->map(function (int $offset) {
            $monthStart = now()->subMonths($offset)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $bookingCount = Booking::query()
                ->whereNotIn('payment_status', ['pending', 'failed'])
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
            $packageCount = PackageBooking::query()
                ->whereNotIn('payment_status', ['pending', 'failed'])
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            return [
                'Month' => $monthStart->format('M Y'),
                'Bookings' => $bookingCount,
                'Package Bookings' => $packageCount,
                'Total' => $bookingCount + $packageCount,
            ];
        })->values()->all();

        $generatedAt = now()->format('M d, Y H:i:s');
        $filename = 'dashboard-analytics-' . now()->format('Y-m-d-His') . '.xls';

        $html = $this->renderExcelWorkbook([
            [
                'title' => 'Summary',
                'headers' => ['Metric', 'Value'],
                'rows' => [
                    ['Metric' => 'Report Range', 'Value' => $rangeLabel],
                    ['Metric' => 'Revenue Date Basis', 'Value' => 'Reservation service date'],
                    ['Metric' => 'Generated At', 'Value' => $generatedAt],
                    ['Metric' => 'Total Users', 'Value' => $totalUsers],
                    ['Metric' => 'Verified Users', 'Value' => $verifiedUsers],
                    ['Metric' => 'Admin Users', 'Value' => $adminUsers],
                    ['Metric' => 'Client Users', 'Value' => $clientUsers],
                    ['Metric' => 'Total Bookings', 'Value' => $totalBookings],
                    ['Metric' => 'Active Paid Bookings', 'Value' => $activeBookings],
                    ['Metric' => 'Completed Paid Bookings', 'Value' => $completedBookings],
                    ['Metric' => 'Gross Booking Revenue', 'Value' => $grossRevenue],
                    ['Metric' => 'Net Booking Revenue', 'Value' => $netRevenue],
                    ['Metric' => 'Live Pending Value', 'Value' => $pendingRevenue],
                    ['Metric' => 'Live Pending Bookings', 'Value' => $pendingBookings],
                    ['Metric' => 'Paid Completed Booking Revenue', 'Value' => $completedRevenue],
                    ['Metric' => 'Refunded Revenue', 'Value' => $refundedRevenue],
                    ['Metric' => 'Total Products', 'Value' => $totalProducts],
                    ['Metric' => 'Total Foods', 'Value' => $totalFoods],
                    ['Metric' => 'Low Stock Products', 'Value' => $lowStockProducts],
                    ['Metric' => 'Low Stock Foods', 'Value' => $lowStockFoods],
                ],
            ],
            [
                'title' => 'Revenue Analytics',
                'headers' => ['Metric', 'Value'],
                'rows' => $revenueAnalyticsRows,
            ],
            [
                'title' => 'Booking Breakdown',
                'headers' => ['Metric', 'Value'],
                'rows' => $bookingTypeRows,
            ],
            [
                'title' => 'Non-attempt Booking Records Created (Last 12 Months)',
                'headers' => ['Month', 'Bookings', 'Package Bookings', 'Total'],
                'rows' => $monthlyBookings,
            ],
            [
                'title' => 'Raw Bookings',
                'headers' => ['Type', 'Customer', 'Email', 'Phone', 'Reference', 'Check-in', 'Check-out', 'Status', 'Payment', 'Amount', 'Created At'],
                'rows' => $bookingRows->all(),
            ],
            [
                'title' => 'Raw Package Bookings',
                'headers' => ['Type', 'Customer', 'Email', 'Phone', 'Reference', 'Check-in', 'Check-out', 'Status', 'Payment', 'Amount', 'Created At'],
                'rows' => $packageRows->all(),
            ],
        ], $rangeLabel, $generatedAt);

        return response($html, 200)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function resolveDateRange(Request $request): array
    {
        $range = $request->string('range', '30d')->toString();
        if (!$request->has('range') && $request->filled('from') && $request->filled('to')) {
            $range = 'custom';
        }
        if (! in_array($range, ['today', '7d', '30d', 'custom'], true)) {
            $range = '30d';
        }

        $end = now()->endOfDay();
        $start = null;
        $label = 'Last 30 Days';

        if ($range === 'today') {
            $start = now()->startOfDay();
            $label = 'Today';
        }

        if ($range === '7d') {
            $start = now()->subDays(6)->startOfDay();
            $label = 'Last 7 Days';
        }

        if ($range === '30d') {
            $start = now()->subDays(29)->startOfDay();
            $label = 'Last 30 Days';
        }

        if ($range === 'custom') {
            $request->validate([
                'from' => ['required', 'date'],
                'to' => ['required', 'date', 'after_or_equal:from'],
            ]);

            $start = Carbon::parse($request->from)->startOfDay();
            $end = Carbon::parse($request->to)->endOfDay();
            $label = $start->format('M d, Y') . ' - ' . $end->format('M d, Y');
        }

        return [$start, $end, $label, $range];
    }

    private function applyServiceDateRange(Builder $query, string $dateColumn, ?Carbon $start, ?Carbon $end): Builder
    {
        if (! $start || ! $end) {
            return $query;
        }

        return $query
            ->where($dateColumn, '>=', $start->toDateString())
            ->where($dateColumn, '<', $end->copy()->addDay()->toDateString());
    }

    private function getMonthlyBookingCounts(Builder $query)
    {
        $driver = DB::connection()->getDriverName();
        $yearExpr = $driver === 'sqlite' ? "CAST(strftime('%Y', created_at) AS INTEGER)" : 'YEAR(created_at)';
        $monthExpr = $driver === 'sqlite' ? "CAST(strftime('%m', created_at) AS INTEGER)" : 'MONTH(created_at)';

        return $query
            ->selectRaw("{$yearExpr} as year, {$monthExpr} as month, COUNT(*) as count")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    private function buildBookingOverviewSeries(?Carbon $start = null, ?Carbon $end = null, string $salesView = 'daily'): \Illuminate\Support\Collection
    {
        $start = ($start?->copy() ?? now()->subDays(29))->startOfDay();
        $end = ($end?->copy() ?? now())->endOfDay();
        $salesView = $this->normalizeSalesView($salesView);

        $pendingCutoff = now()->subMinutes(60);
        $bookingCounts = $this->getDailyLifecycleOverviewCounts(
            Booking::query()->whereBetween('created_at', [$start, $end]),
            $pendingCutoff
        )->keyBy('day');
        $packageCounts = $this->getDailyLifecycleOverviewCounts(
            PackageBooking::query()->whereBetween('created_at', [$start, $end]),
            $pendingCutoff
        )->keyBy('day');

        $dailySeries = collect(CarbonPeriod::create($start, '1 day', $end))->map(function (Carbon $date) use ($bookingCounts, $packageCounts) {
            $key = $date->toDateString();
            $bookingRow = $bookingCounts->get($key);
            $packageRow = $packageCounts->get($key);

            $confirmed = (int) ($bookingRow->confirmed_count ?? 0) + (int) ($packageRow->confirmed_count ?? 0);
            $completed = (int) ($bookingRow->completed_count ?? 0) + (int) ($packageRow->completed_count ?? 0);
            $livePending = (int) ($bookingRow->pending_count ?? 0) + (int) ($packageRow->pending_count ?? 0);
            $closed = (int) ($bookingRow->closed_count ?? 0) + (int) ($packageRow->closed_count ?? 0);

            return (object) [
                'key' => $key,
                'label' => $date->format('M d'),
                'upcoming' => $confirmed,
                'completed' => $completed,
                'active' => $livePending,
                'cancelled' => $closed,
                'total' => $confirmed + $completed + $livePending + $closed,
            ];
        })->values();

        if ($salesView === 'daily') {
            return $dailySeries;
        }

        $periodStart = match ($salesView) {
            'weekly' => $start->copy()->startOfWeek(),
            'monthly' => $start->copy()->startOfMonth(),
            default => $start->copy()->startOfDay(),
        };
        $periodEnd = match ($salesView) {
            'weekly' => $end->copy()->endOfWeek(),
            'monthly' => $end->copy()->endOfMonth(),
            default => $end->copy()->endOfDay(),
        };
        $interval = match ($salesView) {
            'weekly' => '1 week',
            'monthly' => '1 month',
            default => '1 day',
        };

        $buckets = collect(CarbonPeriod::create($periodStart, $interval, $periodEnd))->mapWithKeys(function (Carbon $date) use ($salesView) {
            $bucketDate = $this->salesBucketDate($date, $salesView);
            $key = $bucketDate->format('Y-m-d');

            return [$key => (object) [
                'key' => $key,
                'label' => $this->salesBucketLabel($bucketDate, $salesView),
                'upcoming' => 0,
                'completed' => 0,
                'active' => 0,
                'cancelled' => 0,
                'total' => 0,
            ]];
        })->all();

        foreach ($dailySeries as $row) {
            $date = Carbon::parse($row->key);
            $bucketDate = $this->salesBucketDate($date, $salesView);
            $key = $bucketDate->format('Y-m-d');

            if (! isset($buckets[$key])) {
                $buckets[$key] = (object) [
                    'key' => $key,
                    'label' => $this->salesBucketLabel($bucketDate, $salesView),
                    'upcoming' => 0,
                    'completed' => 0,
                    'active' => 0,
                    'cancelled' => 0,
                    'total' => 0,
                ];
            }

            $buckets[$key]->upcoming += (int) $row->upcoming;
            $buckets[$key]->completed += (int) $row->completed;
            $buckets[$key]->active += (int) $row->active;
            $buckets[$key]->cancelled += (int) $row->cancelled;
            $buckets[$key]->total += (int) $row->total;
        }

        return collect($buckets)->sortBy('key')->values();
    }

    private function getDailyLifecycleOverviewCounts(Builder $query, Carbon $pendingCutoff): \Illuminate\Support\Collection
    {
        $driver = DB::connection()->getDriverName();
        $dayExpr = $driver === 'sqlite' ? 'date(created_at)' : 'DATE(created_at)';

        return $query
            ->selectRaw("
                {$dayExpr} as day,
                SUM(CASE WHEN status = 'confirmed' AND payment_status = 'paid' THEN 1 ELSE 0 END) as confirmed_count,
                SUM(CASE WHEN status = 'completed' AND payment_status = 'paid' THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE
                    WHEN payment_status = 'pending'
                        AND status NOT IN ('cancelled', 'completed')
                        AND created_at >= ?
                    THEN 1 ELSE 0
                END) as pending_count,
                SUM(CASE
                    WHEN (
                        status = 'confirmed'
                        AND payment_status = 'paid'
                    ) OR (
                        status = 'completed'
                        AND payment_status = 'paid'
                    ) OR (
                        payment_status = 'pending'
                        AND status NOT IN ('cancelled', 'completed')
                        AND created_at >= ?
                    )
                    THEN 0 ELSE 1
                END) as closed_count
            ", [$pendingCutoff, $pendingCutoff])
            ->groupBy('day')
            ->orderBy('day')
            ->get();
    }

    private function getPaidRevenueForRange(Carbon $start, Carbon $end): float
    {
        $bookingRevenue = $this->applyServiceDateRange(Booking::query(), 'checkin_date', $start, $end)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $packageRevenue = $this->applyServiceDateRange(PackageBooking::query(), 'booking_date', $start, $end)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        return (float) $bookingRevenue + (float) $packageRevenue;
    }

    private function resolveRecentBookingImage(object $booking, \Illuminate\Support\Collection $staycations, \Illuminate\Support\Collection $dayTours): ?string
    {
        if ($booking instanceof PackageBooking) {
            return $booking->package?->image
                ?: $booking->package?->images?->first()?->image_path;
        }

        if (! $booking instanceof Booking) {
            return null;
        }

        $bookingData = $booking->booking_data ?? [];

        if ($booking->isStaycationFamilyBooking()) {
            $firstAccommodation = collect($bookingData['accommodations'] ?? [])->first(fn ($accommodation) => is_array($accommodation));
            if (is_array($firstAccommodation)) {
                $embeddedImage = trim((string) (
                    data_get($firstAccommodation, 'staycation.image')
                    ?? data_get($firstAccommodation, 'staycation.swiper_images.0.image_path')
                    ?? $firstAccommodation['image']
                    ?? $firstAccommodation['image_path']
                    ?? ''
                ));

                if ($embeddedImage !== '') {
                    return $embeddedImage;
                }

                $staycationId = $firstAccommodation['room_id'] ?? $firstAccommodation['id'] ?? null;
                if ($staycationId && $staycations->has($staycationId)) {
                    $staycation = $staycations->get($staycationId);

                    return $staycation?->image
                        ?: $staycation?->swiperImages?->first()?->image_path;
                }
            }
        }

        $firstPackage = collect($bookingData['packages'] ?? [])->first(fn ($package) => is_array($package));
        if (is_array($firstPackage)) {
            $embeddedImage = trim((string) (
                $firstPackage['image']
                ?? $firstPackage['image_path']
                ?? data_get($firstPackage, 'package.image')
                ?? data_get($firstPackage, 'swiper_images.0.image_path')
                ?? ''
            ));

            if ($embeddedImage !== '') {
                return $embeddedImage;
            }

            $dayTourId = $firstPackage['package_id'] ?? $firstPackage['daytour_id'] ?? $firstPackage['id'] ?? null;
            if ($dayTourId && $dayTours->has($dayTourId)) {
                $dayTour = $dayTours->get($dayTourId);

                return $dayTour?->image
                    ?: $dayTour?->swiperImages?->first()?->image_path;
            }
        }

        $dayTourId = $bookingData['package_id'] ?? null;
        if ($dayTourId && $dayTours->has($dayTourId)) {
            $dayTour = $dayTours->get($dayTourId);

            return $dayTour?->image
                ?: $dayTour?->swiperImages?->first()?->image_path;
        }

        return null;
    }

    private function resolveRecentBookingItemName(object $booking, \Illuminate\Support\Collection $staycations, \Illuminate\Support\Collection $dayTours): ?string
    {
        if ($booking instanceof PackageBooking) {
            return $booking->package?->name ?: 'Selected Package';
        }

        if (! $booking instanceof Booking) {
            return null;
        }

        $bookingData = $booking->booking_data ?? [];

        if ($booking->isStaycationFamilyBooking()) {
            $firstAccommodation = collect($bookingData['accommodations'] ?? [])->first(fn ($accommodation) => is_array($accommodation));
            if (is_array($firstAccommodation)) {
                $embeddedName = trim((string) (
                    data_get($firstAccommodation, 'staycation.name')
                    ?? $firstAccommodation['name']
                    ?? $firstAccommodation['accommodation_name']
                    ?? ''
                ));

                if ($embeddedName !== '') {
                    return $embeddedName;
                }

                $staycationId = $firstAccommodation['room_id'] ?? $firstAccommodation['id'] ?? null;
                if ($staycationId && $staycations->has($staycationId)) {
                    return $staycations->get($staycationId)?->name;
                }
            }
        }

        $firstPackage = collect($bookingData['packages'] ?? [])->first(fn ($package) => is_array($package));
        if (is_array($firstPackage)) {
            $embeddedName = trim((string) ($firstPackage['name'] ?? ''));
            if ($embeddedName !== '') {
                return $embeddedName;
            }

            $dayTourId = $firstPackage['package_id'] ?? $firstPackage['daytour_id'] ?? $firstPackage['id'] ?? null;
            if ($dayTourId && $dayTours->has($dayTourId)) {
                return $dayTours->get($dayTourId)?->name;
            }
        }

        $dayTourId = $bookingData['package_id'] ?? null;
        if ($dayTourId && $dayTours->has($dayTourId)) {
            return $dayTours->get($dayTourId)?->name;
        }

        return $booking->display_type_label ?? ucfirst((string) $booking->booking_type);
    }

    private function resolveRecentBookingMeta(object $booking): string
    {
        $isPackage = $booking instanceof PackageBooking;
        $bookingDate = $isPackage ? optional($booking->booking_date) : optional($booking->checkin_date);
        $parts = [$bookingDate ? $bookingDate->format('M j, Y') : 'No date'];

        if ($booking instanceof Booking && $booking->isStaycationFamilyBooking() && $booking->display_nights) {
            $nights = (int) $booking->display_nights;
            $parts[] = $nights . ' ' . ($nights === 1 ? 'Night' : 'Nights');
        }

        $guestParts = [];
        $bookingData = $booking->booking_data ?? [];
        $accommodations = collect($bookingData['accommodations'] ?? [])->filter(fn ($a) => is_array($a));

        if ($booking instanceof PackageBooking) {
            $guests = (int) ($booking->total_guests ?? 0);
            if ($guests > 0) {
                $guestParts[] = $guests . ' Guest' . ($guests > 1 ? 's' : '');
            }
        } elseif ($accommodations->isNotEmpty()) {
            $totalAdults = (int) $accommodations->sum(fn ($a) => (int) ($a['adults'] ?? 0));
            $totalChildren = (int) $accommodations->sum(fn ($a) => (int) ($a['children'] ?? $a['children_total'] ?? 0));
            $totalPets = (int) $accommodations->sum(function (array $a) {
                $petCount = $a['num_pets'] ?? $a['pet_count'] ?? null;
                if (is_numeric($petCount)) {
                    return (int) $petCount;
                }

                $petValue = $a['pets'] ?? $a['pet'] ?? null;
                if (is_numeric($petValue)) {
                    return (int) $petValue;
                }

                if (is_string($petValue) && strtolower($petValue) === 'yes') {
                    return 1;
                }

                if (is_bool($petValue) && $petValue) {
                    return 1;
                }

                return 0;
            });
            if ($totalAdults > 0) {
                $guestParts[] = $totalAdults . ' Adult' . ($totalAdults > 1 ? 's' : '');
            }
            if ($totalChildren > 0) {
                $guestParts[] = $totalChildren . ' Child' . ($totalChildren > 1 ? 'ren' : '');
            }
            if ($totalPets > 0) {
                $guestParts[] = $totalPets . ' Pet' . ($totalPets > 1 ? 's' : '');
            }
        }

        if (empty($guestParts)) {
            $guestParts[] = '1 Guest';
        }

        $parts[] = implode(', ', $guestParts);

        return implode(' · ', array_filter($parts));
    }

    private function resolveRecentBookingGuestCount(object $booking): int
    {
        if ($booking instanceof PackageBooking) {
            return max(0, (int) ($booking->total_guests ?? 0));
        }

        if (! $booking instanceof Booking) {
            return 0;
        }

        $bookingData = $booking->booking_data ?? [];
        $guestCount = (int) data_get($bookingData, 'total_guests', data_get($bookingData, 'adults_total', data_get($bookingData, 'guests', 0)));

        if ($guestCount > 0) {
            return $guestCount;
        }

        $accommodations = collect($bookingData['accommodations'] ?? [])->filter(fn ($accommodation) => is_array($accommodation));
        if ($accommodations->isNotEmpty()) {
            $guestCount = (int) $accommodations->sum(function (array $accommodation) {
                return (int) ($accommodation['adults'] ?? 0)
                    + (int) ($accommodation['children'] ?? $accommodation['children_total'] ?? 0);
            });
        }

        if ($guestCount > 0) {
            return $guestCount;
        }

        $selectedPackages = collect($bookingData['packages'] ?? [])->filter(fn ($package) => is_array($package));
        if ($selectedPackages->isNotEmpty()) {
            $guestCount = (int) $selectedPackages->sum(function (array $package) {
                $guests = (int) ($package['guests'] ?? 0);
                $quantity = max(1, (int) ($package['quantity'] ?? 1));

                return $guests > 0 ? ($guests * $quantity) : $quantity;
            });
        }

        return max(0, $guestCount);
    }

    private function buildRevenueSummarySeries(?Carbon $start = null, ?Carbon $end = null): \Illuminate\Support\Collection
    {
        $start = ($start?->copy() ?? now()->subDays(29))->startOfDay();
        $end = ($end?->copy() ?? now())->endOfDay();

        $bookingRows = $this->getDailyRevenueSummaryCounts(
            $this->applyServiceDateRange(Booking::query(), 'checkin_date', $start, $end),
            'checkin_date'
        )->keyBy('day');
        $packageRows = $this->getDailyRevenueSummaryCounts(
            $this->applyServiceDateRange(PackageBooking::query(), 'booking_date', $start, $end),
            'booking_date'
        )->keyBy('day');

        return collect(CarbonPeriod::create($start, '1 day', $end))->map(function (Carbon $date) use ($bookingRows, $packageRows) {
            $key = $date->toDateString();
            $bookingRow = $bookingRows->get($key);
            $packageRow = $packageRows->get($key);

            return (object) [
                'key' => $key,
                'label' => $date->format('M d'),
                'net' => (float) ($bookingRow->net_revenue ?? 0) + (float) ($packageRow->net_revenue ?? 0),
            ];
        })->values();
    }

    private function buildSalesReport(?Carbon $start, ?Carbon $end, string $salesView, float $pendingRevenue = 0.0): array
    {
        $start = $start?->copy() ?? now()->subDays(29)->startOfDay();
        $end = $end?->copy() ?? now()->endOfDay();
        $salesView = $this->normalizeSalesView($salesView);

        $paidEntries = $this->collectSalesEntries($start, $end);
        $refundEntries = $this->collectRefundEntries($start, $end);
        $netRevenue = (float) $paidEntries->sum(fn (object $entry) => (float) $entry->total_amount);
        $refundedRevenue = (float) $refundEntries->sum(fn (object $entry) => (float) $entry->amount);
        $categoryCards = $this->buildSalesCategoryCards($paidEntries, $netRevenue, $refundEntries, $refundedRevenue);
        $trendChartSeries = $this->buildSalesTrendChartSeries($paidEntries, $refundEntries, $salesView, $start, $end);

        $salesReportCards = [
            $this->salesCardData(
                'Net Paid Sales',
                $paidEntries->count(),
                $netRevenue,
                'Bookings currently marked paid in the selected service-date range'
            ),
            $this->salesCardData(
                'Staycation Sales',
                $paidEntries->filter(fn (object $entry) => (float) $entry->staycation_amount > 0)->count(),
                (float) $paidEntries->sum(fn (object $entry) => (float) $entry->staycation_amount),
                'Accommodation revenue from the selected date range'
            ),
            $this->salesCardData(
                'Food & Beverage Sales',
                $paidEntries->filter(fn (object $entry) => (float) $entry->food_amount > 0)->count(),
                (float) $paidEntries->sum(fn (object $entry) => (float) $entry->food_amount),
                'Food add-ons from the selected date range'
            ),
            $this->salesCardData(
                'Activities Sales',
                $paidEntries->filter(fn (object $entry) => (float) $entry->activity_amount > 0)->count(),
                (float) $paidEntries->sum(fn (object $entry) => (float) $entry->activity_amount),
                'Activity add-ons from the selected date range'
            ),
            $this->salesCardData(
                'Paid Booking Records',
                $paidEntries->count(),
                $netRevenue,
                'Bookings currently marked paid in the selected service-date range'
            ),
            $this->salesCardData(
                'Net Booking Revenue',
                $paidEntries->count(),
                $netRevenue,
                'Booking revenue based on service date'
            ),
        ];

        $salesReportSeries = $this->buildSalesTrendSeries($paidEntries, $salesView, $start, $end);
        $salesDistributionSeries = $this->buildSalesDistributionSeries($paidEntries);

        return [
            'cards' => $salesReportCards,
            'series' => $salesReportSeries,
            'distribution' => $salesDistributionSeries,
            'pendingRevenue' => $pendingRevenue,
            'totalTransactions' => $paidEntries->count(),
            'totalAmount' => $netRevenue,
            'trendLabel' => $this->salesTrendLabel($salesView),
            'modeOptions' => $this->salesModeOptions(),
            'categoryCards' => $categoryCards,
            'trendChart' => $trendChartSeries,
        ];
    }

    private function collectRefundEntries(Carbon $start, Carbon $end): \Illuminate\Support\Collection
    {
        $bookings = $this->applyServiceDateRange(Booking::query(), 'checkin_date', $start, $end)
            ->where('payment_status', 'refunded')
            ->get()
            ->map(fn (Booking $booking) => $this->buildRefundSalesEntry($booking))
            ->filter()
            ->values();

        $packageBookings = $this->applyServiceDateRange(PackageBooking::query(), 'booking_date', $start, $end)
            ->where('payment_status', 'refunded')
            ->get()
            ->map(fn (PackageBooking $booking) => $this->buildRefundSalesEntry($booking))
            ->filter()
            ->values();

        return $bookings
            ->concat($packageBookings)
            ->sortByDesc('date')
            ->values();
    }

    private function buildRefundSalesEntry(Booking|PackageBooking $booking): ?object
    {
        $paymentStatus = strtolower((string) ($booking->payment_status ?? ''));

        if ($paymentStatus !== 'refunded') {
            return null;
        }

        $serviceDate = $booking instanceof PackageBooking ? $booking->booking_date : $booking->checkin_date;
        $date = $serviceDate instanceof Carbon ? $serviceDate : Carbon::parse($serviceDate);

        return (object) [
            'date' => $date,
            'amount' => (float) ($booking->total_amount ?? 0),
        ];
    }

    private function buildSalesCategoryCards(\Illuminate\Support\Collection $paidEntries, float $netRevenue, \Illuminate\Support\Collection $refundEntries, float $refundedRevenue): \Illuminate\Support\Collection
    {
        $grossRevenue = $netRevenue + $refundedRevenue;
        $categories = collect([
            [
                'label' => 'Staycation Revenue',
                'amount' => (float) $paidEntries->sum(fn (object $entry) => (float) $entry->staycation_amount),
                'count' => (int) $paidEntries->filter(fn (object $entry) => (float) $entry->staycation_amount > 0)->count(),
                'countLabel' => 'booking',
                'icon' => 'bed-double',
                'tone' => 'bg-blue-50 text-blue-600',
                'percentBase' => $netRevenue,
            ],
            [
                'label' => 'Food & Beverage Revenue',
                'amount' => (float) $paidEntries->sum(fn (object $entry) => (float) $entry->food_amount),
                'count' => (int) $paidEntries->filter(fn (object $entry) => (float) $entry->food_amount > 0)->count(),
                'countLabel' => 'booking',
                'icon' => 'utensils-crossed',
                'tone' => 'bg-emerald-50 text-emerald-600',
                'percentBase' => $netRevenue,
            ],
            [
                'label' => 'Activities Revenue',
                'amount' => (float) $paidEntries->sum(fn (object $entry) => (float) $entry->activity_amount),
                'count' => (int) $paidEntries->filter(fn (object $entry) => (float) $entry->activity_amount > 0)->count(),
                'countLabel' => 'booking',
                'icon' => 'trees',
                'tone' => 'bg-amber-50 text-amber-600',
                'percentBase' => $netRevenue,
            ],
            [
                'label' => 'Refund Amount',
                'amount' => $refundedRevenue,
                'count' => (int) $refundEntries->count(),
                'countLabel' => 'refund',
                'icon' => 'rotate-ccw',
                'tone' => 'bg-violet-50 text-violet-600',
                'percentBase' => $grossRevenue,
            ],
        ]);

        return $categories->map(function (array $category) {
            $base = max(0.0, (float) ($category['percentBase'] ?? 0));
            $percent = $base > 0 ? (($category['amount'] / $base) * 100) : 0;

            return (object) [
                'label' => $category['label'],
                'amount' => (float) $category['amount'],
                'count' => (int) $category['count'],
                'countLabel' => $category['countLabel'],
                'percent' => $percent,
                'icon' => $category['icon'],
                'tone' => $category['tone'],
            ];
        })->values();
    }

    private function buildSalesTrendChartSeries(\Illuminate\Support\Collection $paidEntries, \Illuminate\Support\Collection $refundEntries, string $salesView, Carbon $start, Carbon $end): \Illuminate\Support\Collection
    {
        $salesView = $this->normalizeSalesView($salesView);
        $periodStart = match ($salesView) {
            'weekly' => $start->copy()->startOfWeek(),
            'monthly' => $start->copy()->startOfMonth(),
            default => $start->copy()->startOfDay(),
        };
        $periodEnd = match ($salesView) {
            'weekly' => $end->copy()->endOfWeek(),
            'monthly' => $end->copy()->endOfMonth(),
            default => $end->copy()->endOfDay(),
        };
        $interval = match ($salesView) {
            'weekly' => '1 week',
            'monthly' => '1 month',
            default => '1 day',
        };

        $buckets = collect(CarbonPeriod::create($periodStart, $interval, $periodEnd))->mapWithKeys(function (Carbon $date) use ($salesView) {
            $bucketDate = $this->salesBucketDate($date, $salesView);
            $key = $bucketDate->format('Y-m-d');

            return [$key => (object) [
                'key' => $key,
                'label' => $this->salesBucketLabel($bucketDate, $salesView),
                'staycation' => 0.0,
                'food' => 0.0,
                'activities' => 0.0,
                'refunds' => 0.0,
            ]];
        })->all();

        foreach ($paidEntries as $entry) {
            $date = $entry->date instanceof Carbon ? $entry->date : Carbon::parse($entry->date);
            $bucketDate = $this->salesBucketDate($date, $salesView);
            $key = $bucketDate->format('Y-m-d');

            if (! isset($buckets[$key])) {
                $buckets[$key] = (object) [
                    'key' => $key,
                    'label' => $this->salesBucketLabel($bucketDate, $salesView),
                    'staycation' => 0.0,
                    'food' => 0.0,
                    'activities' => 0.0,
                    'refunds' => 0.0,
                ];
            }

            $buckets[$key]->staycation += (float) ($entry->staycation_amount ?? 0);
            $buckets[$key]->food += (float) ($entry->food_amount ?? 0);
            $buckets[$key]->activities += (float) ($entry->activity_amount ?? 0);
        }

        foreach ($refundEntries as $entry) {
            $date = $entry->date instanceof Carbon ? $entry->date : Carbon::parse($entry->date);
            $bucketDate = $this->salesBucketDate($date, $salesView);
            $key = $bucketDate->format('Y-m-d');

            if (! isset($buckets[$key])) {
                $buckets[$key] = (object) [
                    'key' => $key,
                    'label' => $this->salesBucketLabel($bucketDate, $salesView),
                    'staycation' => 0.0,
                    'food' => 0.0,
                    'activities' => 0.0,
                    'refunds' => 0.0,
                ];
            }

            $buckets[$key]->refunds += (float) ($entry->amount ?? 0);
        }

        return collect($buckets)->sortBy('key')->values();
    }

    private function collectSalesEntries(Carbon $start, Carbon $end): \Illuminate\Support\Collection
    {
        $bookings = $this->applyServiceDateRange(Booking::query(), 'checkin_date', $start, $end)
            ->where('payment_status', 'paid')
            ->get()
            ->map(fn (Booking $booking) => $this->buildBookingSalesEntry($booking))
            ->filter()
            ->values();

        $packageBookings = $this->applyServiceDateRange(PackageBooking::query(), 'booking_date', $start, $end)
            ->where('payment_status', 'paid')
            ->get()
            ->map(fn (PackageBooking $booking) => $this->buildPackageSalesEntry($booking))
            ->filter()
            ->values();

        return $bookings
            ->concat($packageBookings)
            ->sortByDesc('date')
            ->values();
    }

    private function buildBookingSalesEntry(Booking $booking): ?object
    {
        $paymentStatus = strtolower((string) ($booking->payment_status ?? ''));

        if ($paymentStatus !== 'paid') {
            return null;
        }

        $bookingData = is_array($booking->booking_data ?? null) ? ($booking->booking_data ?? []) : [];
        $totals = is_array(data_get($bookingData, 'final_totals', [])) ? data_get($bookingData, 'final_totals', []) : [];
        $foodItems = $this->extractSalesFoods($bookingData);
        $activityItems = $this->extractSalesActivities($bookingData);

        $paidAmount = max(0.0, (float) $booking->total_amount);
        $staycationAmount = max(0.0, (float) data_get($totals, 'accommodation_total', 0));
        $foodAmount = max(0.0, (float) data_get($totals, 'food_total', 0));
        if ($foodAmount <= 0) {
            $foodAmount = (float) collect($foodItems)->sum(fn (array $item) => (float) $item['price'] * (int) $item['quantity']);
        }

        $activityAmount = max(0.0, (float) data_get($totals, 'activities_total', 0));
        if ($activityAmount <= 0) {
            $activityAmount = (float) collect($activityItems)->sum(fn (array $item) => (float) $item['price'] * (int) $item['quantity']);
        }

        if (! in_array($booking->booking_type, Booking::STAYCATION_TYPES, true)) {
            $packageAmount = max(0.0, (float) data_get($bookingData, 'total_price', 0));
            if ($packageAmount <= 0) {
                $packageAmount = (float) collect($bookingData['packages'] ?? [])
                    ->filter(fn ($package) => is_array($package))
                    ->sum(function (array $package) {
                        if (isset($package['total_price'])) {
                            return max(0.0, (float) $package['total_price']);
                        }

                        $price = (float) ($package['price_per_person'] ?? $package['price'] ?? 0);
                        $guests = max(1, (int) ($package['guests'] ?? 1));
                        $quantity = max(1, (int) ($package['quantity'] ?? 1));

                        return max(0.0, $price * $guests * $quantity);
                    });
            }

            $activityAmount += $packageAmount;
        }

        if ($staycationAmount <= 0 && in_array($booking->booking_type, Booking::STAYCATION_TYPES, true)) {
            $staycationAmount = max(0.0, $paidAmount - $foodAmount - $activityAmount);
        }

        $componentTotal = $staycationAmount + $foodAmount + $activityAmount;
        if ($componentTotal <= 0 && $paidAmount > 0) {
            if (in_array($booking->booking_type, Booking::STAYCATION_TYPES, true)) {
                $staycationAmount = $paidAmount;
            } else {
                $activityAmount = $paidAmount;
            }
            $componentTotal = $paidAmount;
        }

        if ($componentTotal > 0 && $paidAmount >= 0) {
            $allocationScale = $paidAmount / $componentTotal;
            $staycationAmount *= $allocationScale;
            $foodAmount *= $allocationScale;
            $activityAmount *= $allocationScale;
        }

        return (object) [
            'date' => $booking->checkin_date instanceof Carbon ? $booking->checkin_date : Carbon::parse($booking->checkin_date),
            'reference' => $booking->transaction_id,
            'staycation_amount' => $staycationAmount,
            'food_amount' => $foodAmount,
            'activity_amount' => $activityAmount,
            'total_amount' => $paidAmount,
        ];
    }

    private function buildPackageSalesEntry(PackageBooking $booking): ?object
    {
        $paymentStatus = strtolower((string) ($booking->payment_status ?? ''));

        if ($paymentStatus !== 'paid') {
            return null;
        }

        $date = $booking->booking_date instanceof Carbon ? $booking->booking_date : Carbon::parse($booking->booking_date);

        return (object) [
            'date' => $date,
            'reference' => $booking->booking_reference,
            'staycation_amount' => 0.0,
            'food_amount' => 0.0,
            'activity_amount' => (float) $booking->total_amount,
            'total_amount' => (float) $booking->total_amount,
        ];
    }

    private function buildSalesTrendSeries(\Illuminate\Support\Collection $entries, string $salesView, Carbon $start, Carbon $end): \Illuminate\Support\Collection
    {
        $salesView = $this->normalizeSalesView($salesView);
        $periodStart = match ($salesView) {
            'weekly' => $start->copy()->startOfWeek(),
            'monthly' => $start->copy()->startOfMonth(),
            default => $start->copy()->startOfDay(),
        };
        $periodEnd = match ($salesView) {
            'weekly' => $end->copy()->endOfWeek(),
            'monthly' => $end->copy()->endOfMonth(),
            default => $end->copy()->endOfDay(),
        };
        $interval = match ($salesView) {
            'weekly' => '1 week',
            'monthly' => '1 month',
            default => '1 day',
        };

        $buckets = collect(CarbonPeriod::create($periodStart, $interval, $periodEnd))->mapWithKeys(function (Carbon $date) use ($salesView) {
            $bucketDate = $this->salesBucketDate($date, $salesView);
            $key = $bucketDate->format('Y-m-d');

            return [$key => (object) [
                'key' => $key,
                'label' => $this->salesBucketLabel($bucketDate, $salesView),
                'staycation' => 0.0,
                'food' => 0.0,
                'activities' => 0.0,
                'total' => 0.0,
            ]];
        })->all();

        foreach ($entries as $entry) {
            $date = $entry->date instanceof Carbon ? $entry->date : Carbon::parse($entry->date);
            $bucketDate = $this->salesBucketDate($date, $salesView);
            $key = $bucketDate->format('Y-m-d');

            if (! isset($buckets[$key])) {
                $buckets[$key] = (object) [
                    'key' => $key,
                    'label' => $this->salesBucketLabel($bucketDate, $salesView),
                    'staycation' => 0.0,
                    'food' => 0.0,
                    'activities' => 0.0,
                    'total' => 0.0,
                ];
            }

            $buckets[$key]->staycation += (float) ($entry->staycation_amount ?? 0);
            $buckets[$key]->food += (float) ($entry->food_amount ?? 0);
            $buckets[$key]->activities += (float) ($entry->activity_amount ?? 0);
            $buckets[$key]->total += (float) ($entry->total_amount ?? 0);
        }

        return collect($buckets)->sortBy('key')->values();
    }

    private function buildSalesDistributionSeries(\Illuminate\Support\Collection $paidEntries): \Illuminate\Support\Collection
    {
        $categories = collect([
            [
                'key' => 'staycation_amount',
                'label' => 'Staycation',
                'color' => '#2563eb',
            ],
            [
                'key' => 'food_amount',
                'label' => 'Food & Beverage',
                'color' => '#059669',
            ],
            [
                'key' => 'activity_amount',
                'label' => 'Activities',
                'color' => '#14b8a6',
            ],
        ]);

        $total = max(0.0, (float) $paidEntries->sum(fn (object $entry) => (float) $entry->staycation_amount + (float) $entry->food_amount + (float) $entry->activity_amount));

        return $categories->map(function (array $category) use ($paidEntries, $total) {
            $amount = (float) $paidEntries->sum(fn (object $entry) => (float) ($entry->{$category['key']} ?? 0));
            $count = (int) $paidEntries->filter(fn (object $entry) => (float) ($entry->{$category['key']} ?? 0) > 0)->count();

            return (object) [
                'label' => $category['label'],
                'amount' => $amount,
                'count' => $count,
                'percent' => $total > 0 ? ($amount / $total) * 100 : 0,
                'color' => $category['color'],
            ];
        })->values();
    }

    private function salesCardData(string $label, int $count, float $amount, string $subtitle): array
    {
        return [
            'label' => $label,
            'count' => $count,
            'amount' => $amount,
            'subtitle' => $subtitle,
        ];
    }

    private function salesModeOptions(): array
    {
        return [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
        ];
    }

    private function salesTrendLabel(string $salesView): string
    {
        return 'Sales trend';
    }

    private function normalizeSalesView(mixed $value): string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['daily', 'weekly', 'monthly'], true) ? $value : 'daily';
    }

    private function chartViewFromRange(string $range, mixed $fallback = 'daily'): string
    {
        return match ($range) {
            'today' => 'daily',
            '7d' => 'weekly',
            '30d' => 'monthly',
            default => $this->normalizeSalesView($fallback),
        };
    }

    private function salesBucketDate(Carbon $date, string $salesView): Carbon
    {
        return match ($salesView) {
            'weekly' => $date->copy()->startOfWeek(),
            'monthly' => $date->copy()->startOfMonth(),
            default => $date->copy()->startOfDay(),
        };
    }

    private function salesBucketLabel(Carbon $date, string $salesView): string
    {
        return match ($salesView) {
            'weekly' => $date->copy()->format('M d'),
            'monthly' => $date->copy()->format('M Y'),
            default => $date->copy()->format('M d'),
        };
    }

    private function extractSalesFoods(array $bookingData): array
    {
        return collect($bookingData['selected_foods'] ?? [])
            ->filter(fn ($food) => is_array($food) && ! empty($food['name']))
            ->map(function (array $food) {
                return [
                    'name' => (string) $food['name'],
                    'quantity' => (int) ($food['quantity'] ?? 1),
                    'price' => (float) ($food['price'] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function extractSalesActivities(array $bookingData): array
    {
        $activities = collect($bookingData['selected_activities'] ?? [])
            ->filter(fn ($activity) => is_array($activity) && ! empty($activity['name']))
            ->map(function (array $activity) {
                return [
                    'name' => (string) $activity['name'],
                    'quantity' => (int) ($activity['guests'] ?? $activity['quantity'] ?? 1),
                    'price' => (float) ($activity['price'] ?? 0),
                ];
            })
            ->values()
            ->all();

        $packages = collect($bookingData['selected_activity_packages'] ?? [])
            ->filter(fn ($package) => is_array($package) && ! empty($package['name']))
            ->map(function (array $package) {
                return [
                    'name' => (string) $package['name'],
                    'quantity' => (int) ($package['quantity'] ?? 1),
                    'price' => (float) ($package['price'] ?? 0),
                ];
            })
            ->values()
            ->all();

        return array_merge($activities, $packages);
    }

    private function getDailyRevenueSummaryCounts(Builder $query, string $dateColumn)
    {
        $driver = DB::connection()->getDriverName();
        $dayExpr = $driver === 'sqlite' ? "date({$dateColumn})" : "DATE({$dateColumn})";

        return $query
            ->selectRaw("
                {$dayExpr} as day,
                SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as net_revenue
            ")
            ->groupBy('day')
            ->orderBy('day')
            ->get();
    }

    private function renderExcelWorkbook(array $sections, string $rangeLabel, string $generatedAt): string
    {
        $html = '<html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;font-size:12px;color:#111;">';
        $html .= '<h1 style="font-size:20px;margin-bottom:6px;">CalaiaPH Admin Dashboard Analytics</h1>';
        $html .= '<p style="margin:0 0 18px 0;">Range: ' . e($rangeLabel) . ' | Generated: ' . e($generatedAt) . '</p>';

        foreach ($sections as $section) {
            $title = e($section['title'] ?? 'Section');
            $headers = $section['headers'] ?? [];
            $rows = $section['rows'] ?? [];

            $html .= '<h2 style="font-size:16px;margin:20px 0 8px 0;">' . $title . '</h2>';
            $html .= '<table border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;width:100%;margin-bottom:18px;">';
            $html .= '<thead><tr style="background:#f3f4f6;">';
            foreach ($headers as $header) {
                $html .= '<th style="text-align:left;">' . e($header) . '</th>';
            }
            $html .= '</tr></thead><tbody>';

            if (empty($rows)) {
                $html .= '<tr><td colspan="' . count($headers) . '">No rows found</td></tr>';
            } else {
                foreach ($rows as $row) {
                    $html .= '<tr>';
                    foreach ($headers as $header) {
                        $value = is_array($row) ? ($row[$header] ?? '') : '';
                        $html .= '<td>' . e($this->formatExcelValue($value)) . '</td>';
                    }
                    $html .= '</tr>';
                }
            }

            $html .= '</tbody></table>';
        }

        $html .= '</body></html>';

        return $html;
    }

    private function formatExcelValue(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('M d, Y H:i');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_float($value) || is_int($value)) {
            return is_float($value)
                ? number_format($value, 2, '.', '')
                : (string) $value;
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    /**
     * Search bookings and users
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search Bookings (use correct column names: customer_* not guest_*)
        $bookings = Booking::where('id', 'like', "%$query%")
            ->orWhere('customer_email', 'like', "%$query%")
            ->orWhere('customer_name', 'like', "%$query%")
            ->orWhere('customer_phone', 'like', "%$query%")
            ->orWhere('transaction_id', 'like', "%$query%")
            ->limit(5)
            ->get(['id', 'customer_name', 'customer_email', 'status', 'created_at', 'transaction_id']);

        foreach ($bookings as $booking) {
            $results[] = [
                'type' => 'booking',
                'id' => $booking->id,
                'title' => "Booking #{$booking->id}",
                'subtitle' => $booking->customer_name . ' (' . $booking->customer_email . ')',
                'status' => $booking->status,
                'url' => route('admin.bookings.show', $booking->id),
            ];
        }

        // Search Users
        $users = User::where('first_name', 'like', "%$query%")
            ->orWhere('last_name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->limit(5)
            ->get(['id', 'first_name', 'last_name', 'email', 'user_type']);

        foreach ($users as $user) {
            $results[] = [
                'type' => 'user',
                'id' => $user->id,
                'title' => $user->first_name . ' ' . $user->last_name,
                'subtitle' => $user->email,
                'badge' => ucfirst($user->user_type),
                'url' => route('admin.users.show', $user->id),
            ];
        }

        return response()->json(['results' => $results]);
    }
}
