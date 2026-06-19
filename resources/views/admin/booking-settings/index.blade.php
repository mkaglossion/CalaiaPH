@extends('layouts.admin')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container mx-auto p-6 space-y-8">

    <h1 class="text-2xl font-bold">Staycation Fees</h1>

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

</div>

<script>
document.getElementById('bookingSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const mattressPrice = document.getElementById('mattress_price_per_night').value;
    const petPrice = document.getElementById('pet_price').value;
    const maxPets = document.getElementById('max_pets_per_room').value;

    // Validation
    if (!mattressPrice || mattressPrice < 0) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Extra Mattress Fee must be a positive number',
            icon: 'error',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        return;
    }

    if (!petPrice || petPrice < 0) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Pet Fee must be a positive number',
            icon: 'error',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        return;
    }

    if (!maxPets || maxPets < 1 || maxPets > 10) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Maximum Pets must be between 1 and 10',
            icon: 'error',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        return;
    }

    // Show loading state
    const saveBtn = document.getElementById('saveBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';

    // Submit the form
    const form = document.getElementById('bookingSettingsForm');
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => Promise.reject(data));
        }
        return response.json();
    })
    .then(data => {
        // Success toast
        Swal.fire({
            title: 'Success!',
            text: 'Booking settings updated successfully.',
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            location.reload();
        });
    })
    .catch(error => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Save Settings';

        const errorMessage = error.message || 'Failed to update settings';
        Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    });
});
</script>

@endsection
