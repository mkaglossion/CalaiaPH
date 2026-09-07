@extends('layouts.admin')

@section('title', 'Staycation Additional Fees')

@section('content')
<div class="min-h-screen space-y-6 rounded-xl bg-linear-to-b from-gray-50 to-gray-100 p-4 md:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 md:text-4xl">Staycation Additional Fees</h1>
            <p class="mt-2 text-sm text-gray-600 md:text-base">Configure extra mattress and pet fees for staycation bookings. These settings apply to all regular accommodations.</p>
        </div>
        <x-admin.action-button variant="soft" href="{{ route('admin.stay.staycations') }}" class="px-4 py-2 text-sm font-semibold">
            Back to Staycations
        </x-admin.action-button>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white/70 p-5 shadow-sm backdrop-blur md:p-6">
        <form id="bookingSettingsForm" action="{{ route('admin.booking-settings.update') }}" method="POST">
            @csrf

            {{-- Fee and Pet Settings --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Extra Mattress Price --}}
                <div>
                    <label for="mattress_price_per_night" class="block text-sm font-semibold text-gray-700 mb-2">
                        Extra Guest Fee (per night)
                    </label>
                    <div class="flex items-center">
                        <span class="text-gray-600 text-lg font-semibold">₱</span>
                        <input
                            type="number"
                            id="mattress_price_per_night"
                            name="mattress_price_per_night"
                            value="{{ $settings->where('key', 'mattress_price_per_night')->first()?->value ?? 1250 }}"
                            step="0.01"
                            min="0"
                            class="ml-2 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-[#8f5d54] focus:outline-none focus:ring-2 focus:ring-[#d8bcb7]"
                            required
                        />
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Auto-applied per guest above the included 4 guests, up to the 6-guest limit.
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
                            class="ml-2 block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-[#8f5d54] focus:outline-none focus:ring-2 focus:ring-[#d8bcb7]"
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
                        class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-[#8f5d54] focus:outline-none focus:ring-2 focus:ring-[#d8bcb7]"
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

            <div class="mt-8 rounded-xl border border-[#dfc8c1] bg-[#f6ede9] p-4">
                <h3 class="mb-2 text-sm font-semibold text-[#774b43]">Important Notes</h3>
                <ul class="list-inside list-disc space-y-1 text-sm text-[#774b43]">
                    <li>Extra mattress fee is auto-computed when total guests exceed room capacity</li>
                    <li>Pet fee is charged per pet for bookings with pets</li>
                    <li>Maximum pets setting enforces limits per room during bookings</li>
                    <li>Changes are immediately reflected in the booking calendar</li>
                    <li>Your changes are automatically saved on refresh</li>
                </ul>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <x-admin.action-button
                    variant="primary"
                    type="submit"
                    id="saveBtn"
                    class="px-6 py-2 text-sm font-semibold"
                >
                    Save Settings
                </x-admin.action-button>
                <x-admin.action-button
                    variant="secondary"
                    href="{{ route('admin.stay.staycations') }}"
                    class="px-6 py-2 text-sm font-semibold"
                >
                    Cancel
                </x-admin.action-button>
            </div>
        </form>
    </div>

@endsection
