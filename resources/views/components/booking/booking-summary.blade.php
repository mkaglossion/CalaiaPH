@props(['datesSelected', 'checkinDate', 'checkoutDate', 'nights', 'bookingType'])

{{-- Right column: Booking Summary --}}
<div class="lg:w-1/3 bg-white rounded-xl shadow-md p-6 lg:sticky lg:top-6 transition-all duration-300">
    <h3 class="text-2xl font-bold text-gray-900 mb-4" style="font-family: 'Gotham Book', sans-serif;">Booking Summary</h3>

    @if($datesSelected)
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Check-in:</span>
                <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($checkinDate)->format('M j, Y') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Check-out:</span>
                <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($checkoutDate)->format('M j, Y') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Nights:</span>
                <span class="text-sm font-medium">{{ $nights }}</span>
            </div>

            <hr class="my-3">

            {{-- Accommodations --}}
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Accommodations:</span>
                <span class="text-sm font-medium" id="selected-accommodations-count">0 selected</span>
            </div>

            {{-- Selected accommodations list --}}
            <div id="selected-accommodations-summary" class="mt-2 space-y-1 max-h-0 overflow-hidden transition-all duration-300">
                <!-- JS will populate -->
            </div>

            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Total Guests:</span>
                <span class="text-sm font-medium" id="total-guests">0</span>
            </div>

            <div class="flex justify-between items-center text-lg font-bold">
                <span>Total:</span>
                <span id="total-price">₱ 0</span>
            </div>
        </div>

        <div class="mt-6 space-y-3">
            <button class="w-full bg-emerald-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed" id="proceed-btn" disabled onclick="proceedToActivities(event)">
                Proceed to Food & Beverages
            </button>

            <!-- <button class="w-full bg-gray-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-gray-700 transition disabled:opacity-50 disabled:cursor-not-allowed" id="skip-btn" disabled onclick="skipToPayment()">
                Skip Activities & Go to Payment
            </button> -->
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="text-center">
                <h4 class="text-sm font-semibold text-gray-900 mb-3" style="font-family: 'Gotham Book', sans-serif;">Secure Booking</h4>
                <div class="flex justify-center items-center space-x-4">
                    <div class="flex items-center">
                        <i class="fas fa-lock text-green-600 mr-2"></i>
                        <span class="text-xs text-gray-600">SSL Secured</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
                        <span class="text-xs text-gray-600">Data Protected</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Your information is safe with us</p>
            </div>
        </div>
    @else
        <p class="text-xs text-gray-500" style="font-family: 'Gotham Book', sans-serif;">Please select your dates to see the booking summary.</p>
    @endif
</div>