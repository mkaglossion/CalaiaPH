@extends('layouts.admin')

@section('title', 'Stay Settings')

@section('content')
<div class="container mx-auto p-6 space-y-8">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">Stay Settings</h1>
            <p class="text-gray-600 text-sm mt-1">Dynamic pricing overview based on season types</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.pricingConfig.index') }}" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 font-semibold">
                ⚙️ Configure Pricing
            </a>
            <a href="{{ route('admin.booking-settings.index') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 font-semibold">
                💰 Additional Fees
            </a>
            <a href="{{ route('admin.stay.staycations') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                ← Back to Staycations
            </a>
        </div>
    </div>

    {{-- Current Season Indicator --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-blue-900 mb-2">Current Season</h3>
        <div class="flex items-center gap-2">
            <span class="text-2xl">{{ $currentSeason }}</span>
            <span class="text-sm text-blue-700">(Today: {{ now()->format('M j, Y') }})</span>
        </div>
    </div>

    {{-- Pricing Overview --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @php
            $seasonTypes = ['Non-Peak', 'Peak', 'Super-Peak'];
            $seasonColors = [
                'Non-Peak' => 'bg-green-50 border-green-200 text-green-900',
                'Peak' => 'bg-yellow-50 border-yellow-200 text-yellow-900',
                'Super-Peak' => 'bg-red-50 border-red-200 text-red-900'
            ];
        @endphp

        @foreach($seasonTypes as $seasonType)
            <div class="bg-white border rounded-lg p-6 {{ $currentSeason === $seasonType ? 'ring-2 ring-blue-500' : '' }}">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold {{ $seasonColors[$seasonType] ?? 'text-gray-900' }}">
                        {{ $seasonType }}
                    </h3>
                    @if($currentSeason === $seasonType)
                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Current</span>
                    @endif
                </div>

                <div class="space-y-3">
                    @foreach($pricingByStaycation as $staycationName => $rates)
                        @php
                            // Map season type to rate key
                            $seasonKeyMap = [
                                'Non-Peak' => 'nonPeak',
                                'Peak' => 'peak',
                                'Super-Peak' => 'superPeak'
                            ];
                            $seasonKey = $seasonKeyMap[$seasonType] ?? 'nonPeak';
                            $price = $rates[$seasonKey] ?? 0;
                        @endphp

                        <div class="border rounded p-3">
                            <div class="font-medium text-sm text-gray-800 mb-1">{{ $staycationName }}</div>
                            <div class="text-lg font-bold text-green-600">
                                ₱{{ number_format($price, 0) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Staycations Reference --}}
    <div class="bg-white border rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Configured Pricing by Staycation Type</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Staycation Type</th>
                        <th class="px-4 py-2 text-right">Non-Peak</th>
                        <th class="px-4 py-2 text-right">Peak</th>
                        <th class="px-4 py-2 text-right">Super-Peak</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pricingByStaycation as $staycationName => $rates)
                        <tr class="border-b">
                            <td class="px-4 py-2 font-medium">{{ $staycationName }}</td>
                            <td class="px-4 py-2 text-right text-green-600 font-semibold">₱{{ number_format($rates['nonPeak'], 0) }}</td>
                            <td class="px-4 py-2 text-right text-yellow-600 font-semibold">₱{{ number_format($rates['peak'], 0) }}</td>
                            <td class="px-4 py-2 text-right text-red-600 font-semibold">₱{{ number_format($rates['superPeak'], 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


</div>
@endsection