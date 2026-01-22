@extends('layouts.admin')

@section('title', 'Inventory Monitoring')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-semibold text-gray-800">Inventory Monitoring</h1>
        <div class="flex gap-3">
            <button onclick="window.location.reload()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
            <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                <i class="fas fa-arrow-left mr-2"></i>Back to Bookings
            </a>
        </div>
    </div>

    <!-- Sync Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-600 text-xl mr-3"></i>
            <div>
                <p class="font-semibold text-blue-900">Inventory Sync Command</p>
                <p class="text-sm text-blue-700 mt-1">Run <code class="bg-blue-100 px-2 py-1 rounded">php artisan staycation:sync-inventory --days=90</code> to sync inventory for the next 90 days</p>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white p-6 rounded-lg shadow">
        <form method="GET" action="{{ route('admin.bookings.inventory') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-[#8f5d54] text-white rounded-lg hover:bg-[#7a4d45]">
                    <i class="fas fa-filter mr-2"></i>Apply Filter
                </button>
                <a href="{{ route('admin.bookings.inventory') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Pagination Controls -->
    @if(isset($pagination))
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between bg-white border border-gray-200 rounded-lg px-4 py-3 shadow">
        <div class="text-sm text-gray-700">
            Showing <span class="font-semibold">{{ $pagination->firstItem() }}</span> to <span class="font-semibold">{{ $pagination->lastItem() }}</span> of <span class="font-semibold">{{ $pagination->total() }}</span> days
        </div>
        <div>
            {{ $pagination->appends(request()->except('page'))->links('pagination::tailwind') }}
        </div>
    </div>
    @endif

    <!-- Overall Total Available Slots per Day -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-linear-to-r from-[#8f5d54] to-[#7a4d45] px-6 py-4">
            <h2 class="text-xl font-semibold text-white">Overall Total Available Slots per Day</h2>
            <p class="text-sm text-white/80 mt-1">Sum of available slots across all staycations for each date</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Available Slots</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilization</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dailyTotals as $daily)
                        @php
                            $date = \Carbon\Carbon::parse($daily['date']);
                            $utilization = $daily['total_quantity'] > 0
                                ? ($daily['booked_quantity'] / $daily['total_quantity']) * 100
                                : 0;
                            $isToday = $date->isToday();
                            $isPast = $date->isPast() && !$isToday;
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $isToday ? 'bg-yellow-50' : ($isPast ? 'bg-gray-50' : '') }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $date->format('M d, Y') }}</div>
                                @if($isToday)
                                    <span class="text-xs text-yellow-600 font-semibold">Today</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $date->format('l') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold {{ $daily['available_quantity'] == 0 ? 'text-red-600' : ($daily['available_quantity'] <= 2 ? 'text-yellow-600' : 'text-green-600') }}">
                                    {{ $daily['available_quantity'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($daily['available_quantity'] == 0)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Fully Booked</span>
                                @elseif($daily['available_quantity'] <= 2)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Limited</span>
                                @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Available</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="h-2 rounded-full {{ $utilization >= 100 ? 'bg-red-600' : ($utilization >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                            style="width: {{ min($utilization, 100) }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-600">{{ number_format($utilization, 0) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-calendar-times text-3xl mb-2"></i>
                                <p>No inventory data for this date range</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inventory per Staycation -->
    @foreach($inventoryData as $data)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-linear-to-r from-[#8f5d54] to-[#7a4d45] px-6 py-4">
            <h2 class="text-xl font-semibold text-white">{{ $data['staycation']->name }}</h2>
            <p class="text-sm text-white/80 mt-1">Available slots for this staycation</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Slots</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilization</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data['inventories'] as $inventory)
                    @php
                        $date = \Carbon\Carbon::parse($inventory->date);
                        $utilization = $inventory->total_quantity > 0
                            ? ($inventory->booked_quantity / $inventory->total_quantity) * 100
                            : 0;
                        $isToday = $date->isToday();
                        $isPast = $date->isPast() && !$isToday;
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $isToday ? 'bg-yellow-50' : ($isPast ? 'bg-gray-50' : '') }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $date->format('M d, Y') }}</div>
                            @if($isToday)
                            <span class="text-xs text-yellow-600 font-semibold">Today</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $date->format('l') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold {{ $inventory->available_quantity == 0 ? 'text-red-600' : ($inventory->available_quantity <= 2 ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ $inventory->available_quantity }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($inventory->available_quantity == 0)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Fully Booked</span>
                            @elseif($inventory->available_quantity <= 2)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Limited</span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Available</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="h-2 rounded-full {{ $utilization >= 100 ? 'bg-red-600' : ($utilization >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                        style="width: {{ min($utilization, 100) }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">{{ number_format($utilization, 0) }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-calendar-times text-3xl mb-2"></i>
                            <p>No inventory data for this date range</p>
                        </td>
                    </tr>
                @endforelse
            </table>
        </div>

        <!-- Summary Stats -->
        @if($data['inventories']->count() > 0)
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Total Days</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $data['inventories']->count() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total Booked</p>
                    <p class="text-lg font-semibold text-red-600">{{ $data['inventories']->sum('booked_quantity') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total Available</p>
                    <p class="text-lg font-semibold text-green-600">{{ $data['inventories']->sum('available_quantity') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Avg. Utilization</p>
                    @php
                        $totalCapacity = $data['inventories']->sum('total_quantity');
                        $totalBooked = $data['inventories']->sum('booked_quantity');
                        $avgUtilization = $totalCapacity > 0 ? ($totalBooked / $totalCapacity) * 100 : 0;
                    @endphp
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($avgUtilization, 1) }}%</p>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endforeach

    @if(count($inventoryData) == 0)
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">No Inventory Data</h3>
        <p class="text-gray-500">Run the sync command to populate inventory data</p>
    </div>
    @endif
</div>
@endsection
