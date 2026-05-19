@extends('layouts.admin')

@section('title', 'Inventory Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-semibold text-gray-800"><i class="fas fa-boxes text-brand mr-3"></i>Inventory Management</h1>
        <div class="flex gap-3">
            <button onclick="window.location.reload()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
            <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                <i class="fas fa-arrow-left mr-2"></i>Back to Bookings
            </a>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-600 text-xl mr-3"></i>
            <div>
                <p class="font-semibold text-blue-900">Unified Inventory Management</p>
                <p class="text-sm text-blue-700 mt-1">View and manage availability for staycations, packages, and day tours in one place</p>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white p-6 rounded-lg shadow">
        <form method="GET" action="{{ route('admin.bookings.inventory') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" id="date_from" name="date_from" value="{{ ($dateFrom ?? now())->format('Y-m-d') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" id="date_to" name="date_to" value="{{ ($dateTo ?? now()->addDays(30))->format('Y-m-d') }}" 
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

<div 
    x-data="unifiedInventoryManager()"
    class="space-y-6"
>

    <!-- Tab Navigation -->
    <div class="mb-6 border-b border-gray-200 bg-white rounded-t-xl shadow-sm">
        <nav class="flex gap-8 px-6" role="tablist">
            <button
                @click="activeTab = 'staycations'"
                :class="activeTab === 'staycations' ? 'border-b-2 border-brand text-brand' : 'text-gray-600 hover:text-gray-900'"
                class="py-4 font-semibold transition relative"
                role="tab">
                <i class="fas fa-bed mr-2"></i>Staycations
                @if($staycations->count() > 0)
                    <span class="ml-2 inline-block bg-brand/10 text-brand px-2 py-0.5 rounded text-xs font-bold">
                        {{ $staycations->count() }}
                    </span>
                @endif
            </button>
            <button
                @click="activeTab = 'packages'"
                :class="activeTab === 'packages' ? 'border-b-2 border-brand text-brand' : 'text-gray-600 hover:text-gray-900'"
                class="py-4 font-semibold transition relative"
                role="tab">
                <i class="fas fa-gift mr-2"></i>Packages
                @if($packages->count() > 0)
                    <span class="ml-2 inline-block bg-brand/10 text-brand px-2 py-0.5 rounded text-xs font-bold">
                        {{ $packages->count() }}
                    </span>
                @endif
            </button>
            <button
                @click="activeTab = 'dayTours'"
                :class="activeTab === 'dayTours' ? 'border-b-2 border-brand text-brand' : 'text-gray-600 hover:text-gray-900'"
                class="py-4 font-semibold transition relative"
                x-show="false"
                role="tab">
                <i class="fas fa-map-marker-alt mr-2"></i>Day Tours
                @if($dayTours->count() > 0)
                    <span class="ml-2 inline-block bg-brand/10 text-brand px-2 py-0.5 rounded text-xs font-bold">
                        {{ $dayTours->count() }}
                    </span>
                @endif
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="bg-white rounded-b-xl shadow-sm">
        <!-- STAYCATIONS TAB -->
        <div x-show="activeTab === 'staycations'" x-transition class="p-6">
            @include('admin.inventory.partials.staycations-inventory')
        </div>

        <!-- PACKAGES TAB -->
        <div x-show="activeTab === 'packages'" x-transition class="p-6">
            @include('admin.inventory.partials.packages-inventory')
        </div>

        <!-- DAY TOURS TAB -->
        {{-- <div x-show="activeTab === 'dayTours'" x-transition class="p-6">
            @include('admin.inventory.partials.daytours-inventory')
        </div> --}}
    </div>
</div>

</div>  {{-- Close space-y-6 wrapper --}}
</div>  {{-- Close space-y-6 main wrapper --}}

<script>
function unifiedInventoryManager() {
    return {
        activeTab: 'staycations',
    };
}
</script>
@endsection
