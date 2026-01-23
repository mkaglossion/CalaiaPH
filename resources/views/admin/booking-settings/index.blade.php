@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6 space-y-8">

    <h1 class="text-2xl font-bold">Booking Settings</h1>
    
    <p class="text-gray-600 text-sm mb-6">Configure pricing parameters for staycation bookings. These settings apply to all accommodations except BYOT and Car Camping.</p>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-lg shadow">
        <form action="{{ route('admin.booking-settings.update') }}" method="POST">
            @csrf
            
            {{-- Weekday Rates --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Weekday Rates</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @php
                        $weekdays = ['sun' => 'Sunday', 'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday'];
                    @endphp
                    @foreach($weekdays as $key => $label)
                    <div>
                        <label for="weekday_{{ $key }}_rate" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ $label }} Rate
                        </label>
                        <div class="flex items-center">
                            <span class="text-gray-600 text-lg font-semibold">₱</span>
                            <input
                                type="number"
                                id="weekday_{{ $key }}_rate"
                                name="weekday_{{ $key }}_rate"
                                value="{{ $settings->where('key', 'weekday_' . $key . '_rate')->first()?->value ?? 3000 }}"
                                step="0.01"
                                min="0"
                                class="ml-2 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>
                        @error('weekday_' . $key . '_rate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Weekend and Holiday Rates --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                {{-- Weekend Rate --}}
                <div>
                    <label for="weekend_rate" class="block text-sm font-semibold text-gray-700 mb-2">
                        Weekend Rate
                    </label>
                    <div class="flex items-center">
                        <span class="text-gray-600 text-lg font-semibold">₱</span>
                        <input
                            type="number"
                            id="weekend_rate"
                            name="weekend_rate"
                            value="{{ $settings->where('key', 'weekend_rate')->first()?->value ?? 3500 }}"
                            step="0.01"
                            min="0"
                            class="ml-2 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Applied on weekends (Saturday and Sunday).
                    </p>
                    @error('weekend_rate')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Holiday Rate --}}
                <div>
                    <label for="holiday_rate" class="block text-sm font-semibold text-gray-700 mb-2">
                        Holiday Rate
                    </label>
                    <div class="flex items-center">
                        <span class="text-gray-600 text-lg font-semibold">₱</span>
                        <input
                            type="number"
                            id="holiday_rate"
                            name="holiday_rate"
                            value="{{ $settings->where('key', 'holiday_rate')->first()?->value ?? 4000 }}"
                            step="0.01"
                            min="0"
                            class="ml-2 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Applied on holidays and special dates.
                    </p>
                    @error('holiday_rate')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Peak Rate --}}
                <div>
                    <label for="peak_rate" class="block text-sm font-semibold text-gray-700 mb-2">
                        Peak Season Rate
                    </label>
                    <div class="flex items-center">
                        <span class="text-gray-600 text-lg font-semibold">₱</span>
                        <input
                            type="number"
                            id="peak_rate"
                            name="peak_rate"
                            value="{{ $settings->where('key', 'peak_rate')->first()?->value ?? 4500 }}"
                            step="0.01"
                            min="0"
                            class="ml-2 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Applied during peak season periods.
                    </p>
                    @error('peak_rate')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Super Peak Rate --}}
            <div class="mb-8">
                <div class="max-w-md">
                    <label for="super_peak_rate" class="block text-sm font-semibold text-gray-700 mb-2">
                        Super Peak Season Rate
                    </label>
                    <div class="flex items-center">
                        <span class="text-gray-600 text-lg font-semibold">₱</span>
                        <input
                            type="number"
                            id="super_peak_rate"
                            name="super_peak_rate"
                            value="{{ $settings->where('key', 'super_peak_rate')->first()?->value ?? 5000 }}"
                            step="0.01"
                            min="0"
                            class="ml-2 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Applied during super peak season periods (highest demand).
                    </p>
                    @error('super_peak_rate')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Staycation Type Rates --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Staycation Type Base Rates</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @php
                        $staycationTypes = [
                            'standard_glamping_rate' => ['label' => 'Standard Glamping', 'default' => 6300],
                            'premium_glamping_rate' => ['label' => 'Premium Glamping', 'default' => 7300],
                            'dome_tent_rate' => ['label' => 'Dome Tent', 'default' => 2000],
                            'bell_tent_rate' => ['label' => 'Bell Tent', 'default' => 3800],
                            'byot_rate' => ['label' => 'BYOT', 'default' => 1200],
                            'car_camping_rate' => ['label' => 'Car Camping', 'default' => 2000],
                        ];
                    @endphp
                    @foreach($staycationTypes as $key => $config)
                    <div>
                        <label for="{{ $key }}" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ $config['label'] }} Rate
                        </label>
                        <div class="flex items-center">
                            <span class="text-gray-600 text-lg font-semibold">₱</span>
                            <input
                                type="number"
                                id="{{ $key }}"
                                name="{{ $key }}"
                                value="{{ $settings->where('key', $key)->first()?->value ?? $config['default'] }}"
                                step="0.01"
                                min="0"
                                class="ml-2 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Base rate per night/slot for {{ $config['label'] }}.
                        </p>
                        @error($key)
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Additional Fees --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Extra Mattress Price --}}
                <div>
                    <label for="mattress_price_per_night" class="block text-sm font-semibold text-gray-700 mb-2">
                        Extra Mattress Price (per night)
                    </label>
                    <div class="flex items-center">
                        <span class="text-gray-600 text-lg font-semibold">₱</span>
                        <input
                            type="number"
                            id="mattress_price_per_night"
                            name="mattress_price_per_night"
                            value="{{ $settings->where('key', 'mattress_price_per_night')->first()?->value ?? 800 }}"
                            step="0.01"
                            min="0"
                            class="ml-2 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Applied when customers select extra mattresses. Multiplied by number of mattresses and number of nights.
                    </p>
                    @error('mattress_price_per_night')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pet Price --}}
                <div>
                    <label for="pet_price" class="block text-sm font-semibold text-gray-700 mb-2">
                        Pet Fee (per booking)
                    </label>
                    <div class="flex items-center">
                        <span class="text-gray-600 text-lg font-semibold">₱</span>
                        <input
                            type="number"
                            id="pet_price"
                            name="pet_price"
                            value="{{ $settings->where('key', 'pet_price')->first()?->value ?? 2000 }}"
                            step="0.01"
                            min="0"
                            class="ml-2 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        />
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        One-time fee per booking when customers bring pets. Not per night or per pet count.
                    </p>
                    @error('pet_price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">ℹ️ Important Notes:</h3>
                <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                    <li>These settings apply to all regular room types</li>
                    <li>BYOT and Car Camping accommodations are NOT affected</li>
                    <li>Changes broadcast in real-time to the booking system</li>
                    <li>Frontend calculations update automatically</li>
                </ul>
            </div>

            <div class="mt-6 flex gap-3">
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
                >
                    Save Settings
                </button>
                <a 
                    href="{{ route('admin.stay.staycations') }}" 
                    class="px-6 py-2 bg-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-400 transition"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
