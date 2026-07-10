@extends('layouts.admin')

@section('title', 'Seasonal Pricing')

@section('content')
@php
    $seasonTypes = ['Non-Peak', 'Peak', 'Super-Peak'];
    $seasonAccent = [
        'Non-Peak' => [
            'badge' => 'bg-green-100 text-green-800 border border-green-200',
            'panel' => 'bg-green-50/70 border-green-200/80',
            'price' => 'text-green-700',
        ],
        'Peak' => [
            'badge' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
            'panel' => 'bg-yellow-50/70 border-yellow-200/80',
            'price' => 'text-yellow-700',
        ],
        'Super-Peak' => [
            'badge' => 'bg-red-100 text-red-800 border border-red-200',
            'panel' => 'bg-red-50/70 border-red-200/80',
            'price' => 'text-red-700',
        ],
    ];
@endphp

<div class="p-4 md:p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-gray-900">Seasonal Pricing</h1>
            <p class="mt-2 text-gray-600">Review live staycation rates by season and compare the configured room pricing at a glance.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <x-admin.action-button variant="accent-orange" href="{{ route('admin.seasonDurations.index') }}" class="px-4 py-2 font-medium">
                Season Durations
            </x-admin.action-button>
            <x-admin.action-button variant="accent-green" href="{{ route('admin.booking-settings.index') }}" class="px-4 py-2 font-medium">
                Additional Fees
            </x-admin.action-button>
            <a href="{{ route('admin.stay.staycations') }}" class="px-4 py-2 rounded-xl bg-[#8f5d54] text-white font-medium shadow hover:bg-[#7a4d46] hover:shadow-lg transition">
                Back to Staycations
            </a>
        </div>
    </div>

    <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
        Today's posted prices below use the same pricing computation as the staycation booking availability page.
    </div>

    <div class="bg-white/70 backdrop-blur-lg rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Current Season</h2>
                <p class="text-sm text-gray-500">Active season and day type used for today's published staycation rates.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-sm font-semibold text-blue-900 border border-blue-200">
                    {{ $currentSeason }}
                </span>
                <span class="inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-sm font-semibold text-blue-800 border border-blue-200">
                    {{ $currentDayType }}
                </span>
                <span class="text-sm text-blue-700">Today: {{ now()->format('M j, Y') }}</span>
            </div>
        </div>

        @if($currentSeasonRange)
            <div class="mt-4 rounded-xl border border-blue-200 bg-white/70 px-4 py-3 text-sm text-blue-900">
                Active configured range:
                <span class="font-semibold">{{ $currentSeasonRange['start_date'] }} - {{ $currentSeasonRange['end_date'] }}</span>
                @if(!empty($currentSeasonRange['name']))
                    <span class="text-blue-700">({{ $currentSeasonRange['name'] }})</span>
                @endif
            </div>
        @else
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                No active configured range found for today. Fallback season logic is being used.
            </div>
        @endif
    </div>

    <div class="bg-white/70 backdrop-blur-lg rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Today's Posted Rates</h2>
            <p class="text-sm text-gray-500">Live room prices based on the current season and day type.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($pricingByStaycation as $staycationName => $rates)
                <div class="rounded-2xl border border-gray-200/80 bg-white/75 p-4 shadow-sm">
                    <div class="text-sm font-medium text-gray-800">{{ $staycationName }}</div>
                    <div class="mt-2 text-2xl font-bold text-[#8f5d54]">
                        &#8369;{{ number_format((float) ($rates['currentPostedPrice'] ?? 0), 0) }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        Based on {{ $currentSeason }} / {{ $currentDayType }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        @foreach($seasonTypes as $seasonType)
            @php
                $accent = $seasonAccent[$seasonType] ?? [
                    'badge' => 'bg-gray-100 text-gray-800 border border-gray-200',
                    'panel' => 'bg-white/70 border-gray-200/80',
                    'price' => 'text-gray-700',
                ];
            @endphp
            <div class="bg-white/70 backdrop-blur-lg rounded-2xl shadow-sm border border-gray-200 p-6 {{ $currentSeason === $seasonType ? 'ring-2 ring-blue-200' : '' }}">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold {{ $accent['badge'] }}">
                        {{ $seasonType }}
                    </span>
                    @if($currentSeason === $seasonType)
                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800 border border-blue-200">
                            Current
                        </span>
                    @endif
                </div>

                <div class="space-y-3">
                    @foreach($pricingByStaycation as $staycationName => $rates)
                        @php
                            $seasonKeyMap = [
                                'Non-Peak' => 'nonPeak',
                                'Peak' => 'peak',
                                'Super-Peak' => 'superPeak',
                            ];
                            $seasonKey = $seasonKeyMap[$seasonType] ?? 'nonPeak';
                            $price = $rates[$seasonKey] ?? 0;
                        @endphp

                        <div class="rounded-xl border p-4 shadow-sm {{ $accent['panel'] }}">
                            <div class="text-sm font-medium text-gray-800">{{ $staycationName }}</div>
                            <div class="mt-2 text-lg font-bold {{ $accent['price'] }}">
                                &#8369;{{ number_format((float) $price, 0) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="bg-white/70 backdrop-blur-lg rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Configured Pricing by Staycation Type</h2>
            <p class="text-sm text-gray-500">Comparison table for the saved seasonal rates per room.</p>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
            <table class="w-full min-w-160 text-sm text-left">
                <thead class="bg-gray-100/70 backdrop-blur">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-gray-700">Staycation Type</th>
                        <th class="px-6 py-4 text-right font-semibold text-gray-700">Non-Peak</th>
                        <th class="px-6 py-4 text-right font-semibold text-gray-700">Peak</th>
                        <th class="px-6 py-4 text-right font-semibold text-gray-700">Super-Peak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($pricingByStaycation as $staycationName => $rates)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $staycationName }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-green-700">&#8369;{{ number_format((float) ($rates['nonPeak'] ?? 0), 0) }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-yellow-700">&#8369;{{ number_format((float) ($rates['peak'] ?? 0), 0) }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-red-700">&#8369;{{ number_format((float) ($rates['superPeak'] ?? 0), 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
