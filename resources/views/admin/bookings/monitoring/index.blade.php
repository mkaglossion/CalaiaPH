@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <p class="text-sm text-gray-500">Booking monitoring - {{ ucfirst($mode) }}</p>
            <h1 class="text-3xl font-semibold text-gray-900">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
            <p class="mt-2 text-xs text-gray-400">Today rows are highlighted in amber for quick scanning.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                <span>Back to Bookings</span>
            </a>
            <a href="{{ route($exportRoute, request()->except('page')) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-600 text-white shadow hover:bg-amber-700 transition">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Export CSV</span>
            </a>
            <a href="{{ $clearRoute }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 transition">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span>Reset Filters</span>
            </a>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach($tabs as $tab)
            <a
                href="{{ route($tab['route'], request()->except('page')) }}"
                class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition border {{ $tab['active'] ? 'bg-[#8f5d54] text-white border-[#8f5d54] shadow-sm' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}"
                aria-current="{{ $tab['active'] ? 'page' : 'false' }}"
            >
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($statsCards as $card)
            <x-admin.stat-card
                :label="$card['label']"
                :value="$card['value']"
                :description="$card['subtitle'] ?? ''"
                :icon="$card['icon'] ?? null"
                :icon-bg="$card['iconBg'] ?? 'bg-gray-50'"
                :icon-class="$card['iconClass'] ?? 'text-gray-900'"
                bg="bg-white"
            />
        @endforeach
    </div>

    @include('admin.bookings.monitoring.partials.filters', [
        'search' => $search,
        'statusOptions' => $statusOptions,
        'statusFilter' => $statusFilter,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
    ])

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach($columns as $column)
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($rows as $row)
                        <tr class="{{ $row->row_class ?? '' }}">
                            @foreach($columns as $column)
<td class="px-6 py-4 align-top">
    {!! $row->{$column['key']} ?? '' !!}
</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i data-lucide="search-x" class="w-8 h-8 text-gray-300"></i>
                                    <p class="font-medium">No records match the current filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rows->hasPages())
            <div class="px-4 py-4 border-t border-gray-100">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
