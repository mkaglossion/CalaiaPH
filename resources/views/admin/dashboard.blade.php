@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
  <div class="space-y-6">
    <h1 class="text-3xl font-semibold text-gray-800">Admin Dashboard</h1>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="p-6 bg-white rounded-lg shadow border-l-4 border-blue-500">
        <h3 class="text-lg font-bold text-gray-800">Users</h3>
        <p class="text-3xl font-semibold text-blue-600 mt-2">{{ number_format($clientUsers) }}</p>
        <div class="text-sm text-gray-500 mt-1">
          <p>Total: {{ number_format($totalUsers) }}</p>
          <p>Verified: {{ number_format($verifiedUsers) }}</p>
          <p>Admins: {{ number_format($adminUsers) }}</p>
        </div>
      </div>

      <div class="p-6 bg-white rounded-lg shadow border-l-4 border-green-500">
        <h3 class="text-lg font-bold text-gray-800">Total Bookings</h3>
        <p class="text-3xl font-semibold text-green-600 mt-2">{{ number_format($totalBookings) }}</p>
        <p class="text-sm text-gray-500">Active: {{ number_format($activeBookings) }} | Completed: {{ number_format($completedBookings) }}</p>
      </div>

      <div class="p-6 bg-white rounded-lg shadow border-l-4 border-yellow-500">
        <h3 class="text-lg font-bold text-gray-800">Net Revenue</h3>
        <p class="text-3xl font-semibold text-yellow-600 mt-2">₱{{ number_format($netRevenue, 2) }}</p>
        <div class="text-sm text-gray-500 mt-1">
          <p>Total: ₱{{ number_format($totalRevenue, 2) }}</p>
          @if($refundedRevenue > 0)
            <p class="text-red-600">Refunds: -₱{{ number_format($refundedRevenue, 2) }}</p>
          @endif
          @if($pendingRevenue > 0)
            <p class="text-blue-600">Pending: ₱{{ number_format($pendingRevenue, 2) }}</p>
          @endif
        </div>
      </div>

      <div class="p-6 bg-white rounded-lg shadow border-l-4 border-purple-500">
        <h3 class="text-lg font-bold text-gray-800">Products & Foods</h3>
        <p class="text-3xl font-semibold text-purple-600 mt-2">{{ number_format($totalProducts + $totalFoods) }}</p>
        <p class="text-sm text-gray-500">Products: {{ number_format($totalProducts) }} | Foods: {{ number_format($totalFoods) }}</p>
      </div>
    </div>

    <!-- Inventory Alerts -->
    @if($lowStockProducts > 0 || $lowStockFoods > 0)
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
      <div class="flex">
        <div class="shrink-0">
          <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-yellow-700">
            <strong>Low Stock Alert:</strong>
            @if($lowStockProducts > 0) {{ $lowStockProducts }} product(s) @endif
            @if($lowStockProducts > 0 && $lowStockFoods > 0) and @endif
            @if($lowStockFoods > 0) {{ $lowStockFoods }} food item(s) @endif
            have low stock (≤10 units).
          </p>
        </div>
      </div>
    </div>
    @endif

    <!-- Revenue Analytics -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Revenue Analytics</h3>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="text-center">
            <p class="text-sm text-gray-500">Completed Bookings</p>
            <p class="text-2xl font-bold text-green-600">₱{{ number_format($completedRevenue, 2) }}</p>
          </div>
          <div class="text-center">
            <p class="text-sm text-gray-500">Pending Payments</p>
            <p class="text-2xl font-bold text-blue-600">₱{{ number_format($pendingRevenue, 2) }}</p>
          </div>
          <div class="text-center">
            <p class="text-sm text-gray-500">Total Refunds</p>
            <p class="text-2xl font-bold text-red-600">₱{{ number_format($refundedRevenue, 2) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- User Analytics -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">User Analytics</h3>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="text-center">
            <p class="text-sm text-gray-500">Active Customers</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($clientUsers) }}</p>
          </div>
          <div class="text-center">
            <p class="text-sm text-gray-500">Verified Users</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($verifiedUsers) }}</p>
            @if($verifiedUsers === 0)
              <p class="text-xs text-orange-600 mt-1">Email verification needed</p>
            @endif
          </div>
          <div class="text-center">
            <p class="text-sm text-gray-500">Admin Users</p>
            <p class="text-2xl font-bold text-purple-600">{{ number_format($adminUsers) }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Bookings -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Recent Bookings</h3>
        </div>
        <div class="p-6">
          @if($recentBookings->count() > 0)
            <div class="space-y-4">
              @foreach($recentBookings as $booking)
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ $booking->display_customer_name }}</p>
                    <p class="text-sm text-gray-500">{{ $booking->booking_type }} - {{ $booking->checkin_date->format('M d, Y') }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">₱{{ number_format($booking->total_amount, 2) }}</p>
                    @php
                      $statusClasses = match($booking->status) {
                        'confirmed' => 'bg-green-100 text-green-800',
                        'completed' => 'bg-blue-100 text-blue-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-800'
                      };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                      {{ ucfirst($booking->status) }}
                    </span>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <p class="text-gray-500 text-center py-4">No recent bookings</p>
          @endif
        </div>
      </div>

      <!-- Monthly Bookings Chart -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Monthly Bookings (Last 12 Months)</h3>
        </div>
        <div class="p-6">
          @if($monthlyBookings->count() > 0)
            <div class="space-y-2">
              @foreach($monthlyBookings as $month)
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">{{ \Carbon\Carbon::create($month->year, $month->month)->format('M Y') }}</span>
                  <div class="flex items-center">
                    <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                      <div class="bg-[#8f5d54] h-2 rounded-full" style="width: {{ $monthlyBookings->max('count') > 0 ? ($month->count / $monthlyBookings->max('count')) * 100 : 0 }}%"></div>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ $month->count }}</span>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <p class="text-gray-500 text-center py-4">No booking data available</p>
          @endif
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <a href="{{ route('admin.bookings.index') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <div>
              <p class="font-medium text-blue-900">Manage Bookings</p>
              <p class="text-sm text-blue-700">View and manage all bookings</p>
            </div>
          </a>

          <a href="{{ route('admin.users') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
            <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <div>
              <p class="font-medium text-green-900">Manage Users</p>
              <p class="text-sm text-green-700">View and manage user accounts</p>
            </div>
          </a>

          <a href="{{ route('admin.bookings.inventory') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
            <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <div>
              <p class="font-medium text-purple-900">Inventory</p>
              <p class="text-sm text-purple-700">Check inventory levels</p>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection
