<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use App\Models\Product;
use App\Models\Food;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        [$start, $end, $rangeLabel, $range] = $this->resolveDateRange($request);

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
        // BOOKINGS + REVENUE (1 query)
        // -----------------------------
        $base = Booking::query()
            ->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]));

        $agg = (clone $base)->selectRaw("
            COUNT(*) as total_bookings,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as active_bookings,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,

            SUM(CASE WHEN status != 'cancelled' AND payment_status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status != 'cancelled' AND payment_status = 'pending' THEN total_amount ELSE 0 END) as pending_revenue,
            SUM(CASE WHEN status = 'completed' AND payment_status = 'paid' THEN total_amount ELSE 0 END) as completed_revenue,
            SUM(CASE WHEN status != 'cancelled' AND payment_status = 'refunded' THEN total_amount ELSE 0 END) as refunded_revenue
        ")->first();

        $totalBookings     = (int) $agg->total_bookings;
        $activeBookings    = (int) $agg->active_bookings;
        $completedBookings = (int) $agg->completed_bookings;

        $totalRevenue      = (float) $agg->total_revenue;
        $pendingRevenue    = (float) $agg->pending_revenue;
        $completedRevenue  = (float) $agg->completed_revenue;
        $refundedRevenue   = (float) $agg->refunded_revenue;

        $netRevenue        = $totalRevenue - $refundedRevenue;

        // -----------------------------
        // INVENTORY (2 queries: Products + Foods)
        // -----------------------------
        $productsAgg = Product::query()->selectRaw("
            COUNT(*) as total_products,
            SUM(CASE WHEN is_inventory_managed = 1 AND stock_quantity <= 10 THEN 1 ELSE 0 END) as low_stock_products
        ")->first();

        $foodsAgg = Food::query()->selectRaw("
            COUNT(*) as total_foods,
            SUM(CASE WHEN is_inventory_managed = 1 AND stock_quantity <= 10 THEN 1 ELSE 0 END) as low_stock_foods
        ")->first();

        $totalProducts    = (int) $productsAgg->total_products;
        $lowStockProducts = (int) $productsAgg->low_stock_products;

        $totalFoods    = (int) $foodsAgg->total_foods;
        $lowStockFoods = (int) $foodsAgg->low_stock_foods;

        // -----------------------------
        // RECENT BOOKINGS (1 query)
        // -----------------------------
        $recentBookings = (clone $base)
            ->latest()
            ->take(6)
            ->get();

        // -----------------------------
        // MONTHLY BOOKINGS (1 query)
        // (last 12 months, independent of range filter)
        // -----------------------------
        $monthlyBookings = Booking::query()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('admin.dashboard', compact(
            'rangeLabel', 'start', 'end', 'range',
            'clientUsers', 'totalUsers', 'verifiedUsers', 'adminUsers',
            'totalBookings', 'activeBookings', 'completedBookings',
            'netRevenue', 'totalRevenue', 'pendingRevenue', 'completedRevenue', 'refundedRevenue',
            'totalProducts', 'totalFoods', 'lowStockProducts', 'lowStockFoods',
            'recentBookings', 'monthlyBookings'
        ));
    }

    private function resolveDateRange(Request $request): array
    {
        $range = $request->string('range', '30d')->toString();

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
            $label = $start->format('M d, Y') . ' – ' . $end->format('M d, Y');
        }

        return [$start, $end, $label, $range];
    }
}
