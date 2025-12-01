{{-- resources/views/components/booking-summary.blade.php --}}
@props([
    'bookingData' => [],
    'selectedActivities' => [],
    'title' => 'Booking Summary',
    'badgeText' => '',
    'badgeIcon' => 'ticket-simple',
    'sessionKey' => 'selected_activities'
])

<section
    aria-labelledby="booking-summary-heading"
    class="bg-gray-50 rounded-xl border border-gray-100 p-4 sm:p-5 md:p-6 lg:sticky lg:top-4"
>
    <div class="flex items-center justify-between mb-4">
        <h2 id="booking-summary-heading" class="text-lg font-semibold text-gray-900" style="font-family: 'Gotham Book', sans-serif;">
            {{ $title }}
        </h2>
        @if($badgeText)
            <span class="inline-flex items-center rounded-full bg-[#f4ebe9] px-3 py-1 text-xs font-semibold text-[#8f5d54]">
                <i class="fa-solid fa-{{ $badgeIcon }} mr-1.5"></i>
                {{ $badgeText }}
            </span>
        @endif
    </div>

    <div class="space-y-2 text-sm text-gray-700">
        <div class="flex justify-between gap-4">
            <span class="font-medium text-gray-500">Package</span>
            <span class="text-right">{{ $bookingData['package'] ?? '' }}</span>
        </div>

        <div class="flex justify-between gap-4">
            <span class="font-medium text-gray-500">Price</span>
            <span class="text-right font-semibold text-[#8f5d54]">
                ₱{{ number_format((float) ($bookingData['price'] ?? 0), 2) }}
            </span>
        </div>

        @php
            $activities = $selectedActivities ?: session($sessionKey, []);
            $activitiesTotal = 0;
            foreach ($activities as $activity) {
                $activitiesTotal += $activity['price'] * ($activity['guests'] ?? 1);
            }
            $grandTotal = ($bookingData['price'] ?? 0) + $activitiesTotal;
        @endphp

        @if (!empty($activities))
            <hr class="my-3 border-dashed border-gray-200">

            <div class="space-y-2">
                <p class="font-medium text-gray-700">Selected Activities:</p>
                @foreach ($activities as $activity)
                    <div class="flex justify-between gap-4 pl-4">
                        <span class="text-gray-600">{{ $activity['name'] }} - ₱{{ number_format($activity['price'], 2) }} x {{ $activity['guests'] ?? 1 }}</span>
                        <span class="text-right font-semibold text-[#8f5d54]">
                            ₱{{ number_format($activity['price'] * ($activity['guests'] ?? 1), 2) }}
                        </span>
                    </div>
                @endforeach
            </div>

            <hr class="my-3 border-dashed border-gray-200">

            <div class="flex justify-between gap-4 font-semibold">
                <span class="text-gray-900">Total</span>
                <span class="text-right text-[#8f5d54]">
                    ₱{{ number_format($grandTotal, 2) }}
                </span>
            </div>
        @else
            <hr class="my-3 border-dashed border-gray-200">
        @endif

        <div class="flex justify-between gap-4">
            <span class="font-medium text-gray-500">Guest Name</span>
            <span class="text-right">
                {{ ($bookingData['first_name'] ?? '') . ' ' . ($bookingData['last_name'] ?? '') }}
            </span>
        </div>

        <div class="flex justify-between gap-4">
            <span class="font-medium text-gray-500">Email</span>
            <span class="text-right wrap-break-word">
                {{ $bookingData['email'] ?? '' }}
            </span>
        </div>

        <div class="flex justify-between gap-4">
            <span class="font-medium text-gray-500">Phone</span>
            <span class="text-right">
                {{ $bookingData['phone'] ?? '' }}
            </span>
        </div>

        @if (!empty($bookingData['booking_date']))
            <div class="flex justify-between gap-4">
                <span class="font-medium text-gray-500">Date</span>
                <span class="text-right">
                    {{ \Carbon\Carbon::parse($bookingData['booking_date'])->format('M d, Y') }}
                </span>
            </div>
        @endif

        <div class="flex justify-between gap-4">
            <span class="font-medium text-gray-500">Guests</span>
            <span class="text-right">
                {{ $bookingData['guests'] ?? '' }}
            </span>
        </div>

        @php
            $specialRequests = $bookingData['special_requests'] ?? null;
        @endphp

        @if (!empty($specialRequests))
            <div class="pt-3 mt-2 border-t border-dashed border-gray-200">
                <p class="font-medium text-gray-500 mb-1">Special Requests</p>
                <p class="text-sm text-gray-700 whitespace-pre-line">
                    {{ $specialRequests }}
                </p>
            </div>
        @endif
    </div>

    <div class="mt-4 text-xs text-gray-400" style="font-family: 'Gotham Book', sans-serif;">
        Please review your details carefully before proceeding with payment.
    </div>
</section>