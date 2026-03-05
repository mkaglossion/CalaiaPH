@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6 space-y-8">

    <h1 class="text-2xl font-bold">Booking Pet Fee</h1>

    <p class="text-gray-600 text-sm mb-6">Configure pet fee for staycation bookings. This setting applies to all regular accommodations except BYOT and Car Camping.</p>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-lg shadow">
        <form action="{{ route('admin.booking-settings.update') }}" method="POST">
            @csrf

            {{-- Additional Fees --}}
            <div class="grid grid-cols-1 md:grid-cols-1 gap-8">
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
