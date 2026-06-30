@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6 space-y-8">

    <h1 class="text-2xl font-bold">Staycation Additional Fees</h1>

    <p class="text-gray-600 text-sm mb-6">Configure extra mattress and pet fees for staycation bookings. These settings apply to all regular accommodations.</p>

    <div class="bg-white p-6 rounded-lg shadow">
        <form id="bookingSettingsForm" action="{{ route('admin.booking-settings.update') }}" method="POST">
            @csrf

            {{-- Fee and Pet Settings --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Extra Mattress Price --}}
                <div>
                    <label for="mattress_price_per_night" class="block text-sm font-semibold text-gray-700 mb-2">
                        Extra Mattress Fee (per night)
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
                        Auto-applied per extra mattress per night when guest count exceeds room capacity.
                    </p>
                    @error('mattress_price_per_night')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                {{-- Pet Price --}}
                <div>
                    <label for="pet_price" class="block text-sm font-semibold text-gray-700 mb-2">
                        Pet Fee (per pet)
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
                        Fee charged per pet for each booking.
                    </p>
                    @error('pet_price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Max Pets Per Room --}}
                <div>
                    <label for="max_pets_per_room" class="block text-sm font-semibold text-gray-700 mb-2">
                        Maximum Pets Per Room
                    </label>
                    <input
                        type="number"
                        id="max_pets_per_room"
                        name="max_pets_per_room"
                        value="{{ intval($settings->where('key', 'max_pets_per_room')->first()?->value ?? 2) }}"
                        min="1"
                        max="10"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                    <p class="text-xs text-gray-500 mt-2">
                        Set the maximum number of pets allowed per room (1-10). This setting is dynamic and will update across all bookings.
                    </p>
                    @error('max_pets_per_room')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">ℹ️ Important Notes:</h3>
                <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                    <li>Extra mattress fee is auto-computed when total guests exceed room capacity</li>
                    <li>Pet fee is charged per pet for bookings with pets</li>
                    <li>Maximum pets setting enforces limits per room during bookings</li>
                    <li>Changes are immediately reflected in the booking calendar</li>
                    <li>Your changes are automatically saved on refresh</li>
                </ul>
            </div>

            <div class="mt-6 flex gap-3">
                <button
                    type="submit"
                    id="saveBtn"
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

@endsection
