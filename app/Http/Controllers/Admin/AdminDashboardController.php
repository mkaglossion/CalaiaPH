<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\Product;
use App\Models\Food;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Users - Detailed metrics
        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $clientUsers = User::where('user_type', 'client')->count();
        $adminUsers = User::where('user_type', 'admin')->count();

        // Bookings
        $totalBookings = Booking::count();
        $activeBookings = Booking::where('status', 'confirmed')->count();
        $completedBookings = Booking::where('status', 'completed')->count();

        // Revenue Calculations - Best Practices
        $totalRevenue = Booking::where('payment_status', 'paid')
            ->whereNotIn('status', ['cancelled']) // Exclude cancelled bookings
            ->sum('total_amount');

        $refundedRevenue = Booking::where('payment_status', 'refunded')->sum('total_amount');
        $netRevenue = $totalRevenue - $refundedRevenue;

        // Additional revenue metrics
        $completedRevenue = Booking::where('status', 'completed')
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $pendingRevenue = Booking::where('payment_status', 'pending')
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');

        // Products
        $totalProducts = Product::count();
        $lowStockProducts = Product::where('is_inventory_managed', true)
            ->where('stock_quantity', '<=', 10)
            ->count();

        // Foods
        $totalFoods = Food::count();
        $lowStockFoods = Food::where('is_inventory_managed', true)
            ->where('stock_quantity', '<=', 10)
            ->count();

        // Recent bookings
        $recentBookings = Booking::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Monthly booking trend (last 12 months)
        $monthlyBookings = Booking::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->reverse();

        return view('admin.dashboard', compact(
            'totalUsers',
            'verifiedUsers',
            'clientUsers',
            'adminUsers',
            'totalBookings',
            'activeBookings',
            'completedBookings',
            'totalRevenue',
            'netRevenue',
            'completedRevenue',
            'pendingRevenue',
            'refundedRevenue',
            'totalProducts',
            'lowStockProducts',
            'totalFoods',
            'lowStockFoods',
            'recentBookings',
            'monthlyBookings'
        ));
    }
}
