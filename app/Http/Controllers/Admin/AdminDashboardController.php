<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use App\Models\PackageBooking;
use App\Models\Product;
use App\Models\Food;
use App\Models\AdminSystemAlert;
use App\Models\AdminActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        [$start, $end, $rangeLabel, $range] = $this->resolveDateRange($request);
        $pendingWindowMinutes = 60;
        $pendingCutoff = now()->subMinutes($pendingWindowMinutes);

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
        $base = Booking::query()
            ->whereNotIn('payment_status', ['pending', 'failed'])
            ->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]));
        $packageBase = PackageBooking::query()
            ->whereNotIn('payment_status', ['pending', 'failed'])
            ->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]));
        $pendingBase = Booking::query()
            ->where('payment_status', 'pending')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where('created_at', '>=', $pendingCutoff)
            ->when($start && $end, fn($q) => $q->whereBetween('created_at', [$start, $end]));
        $pendingPackageBase = PackageBooking::query()
            ->where('payment_status', 'pending')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where('created_at', '>=', $pendingCutoff)
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
        $packageAgg = (clone $packageBase)->selectRaw("
            COUNT(*) as total_bookings,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as active_bookings,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,

            SUM(CASE WHEN status != 'cancelled' AND payment_status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = 'completed' AND payment_status = 'paid' THEN total_amount ELSE 0 END) as completed_revenue,
            SUM(CASE WHEN status != 'cancelled' AND payment_status = 'refunded' THEN total_amount ELSE 0 END) as refunded_revenue
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
        $completedRevenue  = (float) $agg->completed_revenue + (float) $packageAgg->completed_revenue;
        $refundedRevenue   = (float) $agg->refunded_revenue + (float) $packageAgg->refunded_revenue;

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
        // RECENT BOOKINGS (2 queries: Booking + PackageBooking)
        // -----------------------------
        $recentBookings = (clone $base)
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($booking) {
                $booking->display_customer_name = $booking->display_customer_name;
                return $booking;
            })
            ->concat(
                (clone $packageBase)
                    ->latest()
                    ->take(6)
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

        return view('admin.dashboard', compact(
            'rangeLabel', 'start', 'end', 'range',
            'clientUsers', 'totalUsers', 'verifiedUsers', 'adminUsers',
            'totalBookings', 'activeBookings', 'completedBookings',
            'netRevenue', 'totalRevenue', 'pendingRevenue', 'pendingBookings', 'pendingWindowMinutes', 'completedRevenue', 'refundedRevenue',
            'totalProducts', 'totalFoods', 'lowStockProducts', 'lowStockFoods',
            'recentBookings', 'monthlyBookings', 'systemAlerts', 'recentActivity'
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
