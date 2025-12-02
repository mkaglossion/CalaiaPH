<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #8f5d54; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .booking-details { background-color: white; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CalaiaPH Booking Receipt</h1>
        </div>

        <div class="content">
            <p>Dear {{ $bookingData['first_name'] ?? $bookingData['customer_info']['first_name'] ?? 'Guest' }} {{ $bookingData['last_name'] ?? $bookingData['customer_info']['last_name'] ?? '' }},</p>

            <p>Your booking has been successfully processed and confirmed. Below is your receipt. Thank you for choosing CalaiaPH!</p>

            <div class="booking-details">
                <h3>Booking Details:</h3>
                <p><strong>Name:</strong> {{ ($bookingData['first_name'] ?? $bookingData['customer_info']['first_name'] ?? '') . ' ' . ($bookingData['last_name'] ?? $bookingData['customer_info']['last_name'] ?? '') }}</p>
                <p><strong>Email:</strong> {{ $bookingData['email'] ?? $bookingData['customer_info']['email'] ?? '' }}</p>
                @php
                $isStaycation = isset($bookingData['accommodations']) && !empty($bookingData['accommodations']);
                $accommodationTotal = 0;
                if ($isStaycation) {
                    // Use stored final totals if available
                    if (isset($bookingData['final_totals'])) {
                        $accommodationTotal = $bookingData['final_totals']['accommodation_total'] ?? 0;
                        $activitiesTotal = $bookingData['final_totals']['activities_total'] ?? 0;
                        $grandTotal = $bookingData['final_totals']['grand_total'] ?? 0;
                    } else {
                        // Fallback calculation if final_totals not set
                        $checkin = !empty($bookingData['checkin']) ? \Carbon\Carbon::parse($bookingData['checkin']) : null;
                        $checkout = !empty($bookingData['checkout']) ? \Carbon\Carbon::parse($bookingData['checkout']) : null;
                        $nights = $checkin && $checkout ? $checkout->diffInDays($checkin) : 0;

                    foreach ($bookingData['accommodations'] as $accommodation) {
                        if (!empty($accommodation['staycation'])) {
                            $quantity = $accommodation['guests'] ?? 1;
                            $name = strtolower($accommodation['staycation']['name']);
                            $accPrice = 0;

                            if ($checkin && $checkout && (str_contains($name, 'standard') || str_contains($name, 'premium'))) {
                                $current = clone $checkin;
                                while ($current < $checkout) {
                                    $day = (int)$current->format('w');
                                    $isWeekend = $day === 5 || $day === 6;
                                    $basePrice = 0;
                                    if (str_contains($name, 'standard')) {
                                        $basePrice = $isWeekend ? 6000 : 4800;
                                    } elseif (str_contains($name, 'premium')) {
                                        $basePrice = $isWeekend ? 7200 : 5760;
                                    }
                                    $extraGuests = max(0, $quantity - 4);
                                    $accPrice += $basePrice + ($extraGuests * 400);
                                    $current->modify('+1 day');
                                }
                            } elseif ($checkin && $checkout && (str_contains($name, 'bell') || str_contains($name, 'dome'))) {
                                $accPrice = 3000 * $nights;
                            } elseif ($checkin && $checkout && (str_contains($name, 'byot') || str_contains($name, 'car camping'))) {
                                $accPrice = 300 * $quantity * $nights;
                            } else {
                                $accPrice = $accommodation['staycation']['price'] * $quantity * $nights;
                            }

                            // Add pet fee
                            if (!empty($accommodation['pet_agreement']) && !empty($accommodation['num_pets'])) {
                                $accPrice += 2000 * $accommodation['num_pets'];
                            }

                            // Add breakfast fee
                            if (!empty($accommodation['has_breakfast'])) {
                                $accPrice += 500 * $accommodation['guests'];
                            }

                            $accommodationTotal += $accPrice;
                        }
                    }
                }
            }
                @endphp
                @if($isStaycation)
                    <p><strong>Booking Type:</strong> Staycation</p>
                    <p><strong>Check-in:</strong> {{ !empty($bookingData['checkin']) ? \Carbon\Carbon::parse($bookingData['checkin'])->format('M d, Y') : '' }}</p>
                    <p><strong>Check-out:</strong> {{ !empty($bookingData['checkout']) ? \Carbon\Carbon::parse($bookingData['checkout'])->format('M d, Y') : '' }}</p>
                    <p><strong>Nights:</strong> {{ !empty($bookingData['checkin']) && !empty($bookingData['checkout']) ? max(1, abs(\Carbon\Carbon::parse($bookingData['checkout'])->diffInDays(\Carbon\Carbon::parse($bookingData['checkin'])))) : 0 }}</p>
                    <p><strong>Guests:</strong> {{ collect($bookingData['accommodations'])->sum('guests') }}</p>
                    @if (!empty($bookingData['accommodations']))
                        <p><strong>Selected Accommodations:</strong></p>
                        <ul>
                            @php
                            $checkin = !empty($bookingData['checkin']) ? \Carbon\Carbon::parse($bookingData['checkin']) : null;
                            $checkout = !empty($bookingData['checkout']) ? \Carbon\Carbon::parse($bookingData['checkout']) : null;
                            if ($checkin && $checkout) {
                                if ($checkout->lt($checkin)) {
                                    $tmp = $checkin;
                                    $checkin = $checkout;
                                    $checkout = $tmp;
                                }
                                $nights = $checkout->diffInDays($checkin);
                                if ($nights === 0) {
                                    $nights = 1;
                                }
                            } else {
                                $nights = 0;
                            }
                            @endphp
                            @foreach ($bookingData['accommodations'] as $accommodation)
                                @if (!empty($accommodation['staycation']))
                                    @php
                                    $quantity = $accommodation['guests'] ?? 1;
                                    $name = strtolower($accommodation['staycation']['name']);
                                    $isBYOT = str_contains($name, 'byot');
                                    $isCarCamping = str_contains($name, 'car camping');
                                    $accommodationPrice = 0;
                                    $basePriceTotal = 0;
                                    $extraPriceTotal = 0;
                                    $extraGuests = 0;

                                    if ($checkin && $checkout && (str_contains($name, 'standard') || str_contains($name, 'premium'))) {
                                        $current = clone $checkin;
                                        while ($current < $checkout) {
                                            $day = (int)$current->format('w');
                                            $isWeekend = $day === 5 || $day === 6;
                                            $basePrice = 0;
                                            if (str_contains($name, 'standard')) {
                                                $basePrice = $isWeekend ? 6000 : 4800;
                                            } elseif (str_contains($name, 'premium')) {
                                                $basePrice = $isWeekend ? 7200 : 5760;
                                            }
                                            $extraGuests = max(0, $quantity - 4);
                                            $accommodationPrice += $basePrice + ($extraGuests * 400);
                                            $basePriceTotal += $basePrice;
                                            $extraPriceTotal += $extraGuests * 400;
                                            $current->modify('+1 day');
                                        }
                                    } elseif ($checkin && $checkout && (str_contains($name, 'bell') || str_contains($name, 'dome'))) {
                                        $accommodationPrice = 3000 * $nights;
                                    } elseif ($checkin && $checkout && (str_contains($name, 'byot') || str_contains($name, 'car camping'))) {
                                        $accommodationPrice = 300 * $quantity * $nights;
                                    } else {
                                        $accommodationPrice = $accommodation['staycation']['price'] * $quantity * $nights;
                                    }

                                    $petFee = (!empty($accommodation['pet_agreement']) && !empty($accommodation['num_pets'])) ? 2000 * $accommodation['num_pets'] : 0;
                                    $breakfastFee = (!empty($accommodation['has_breakfast'])) ? 500 * $accommodation['guests'] : 0;
                                    $totalPrice = $accommodationPrice + $petFee + $breakfastFee;

                                    // Calculate base rate per night (weekday rate for standard/premium)
                                    $baseRate = 0;
                                    if (str_contains($name, 'standard')) {
                                        $baseRate = 4800; // weekday rate
                                    } elseif (str_contains($name, 'premium')) {
                                        $baseRate = 5760; // weekday rate
                                    } elseif (str_contains($name, 'bell') || str_contains($name, 'dome')) {
                                        $baseRate = 3000;
                                    } elseif ($isBYOT || $isCarCamping) {
                                        $baseRate = 300;
                                    }

                                    $extraRate = $extraGuests * 400; // ₱400 per extra guest per night
                                    @endphp
                                    <li>{{ $accommodation['staycation']['name'] }} - ₱{{ number_format($baseRate, 2) }}/night
                                        @if($extraGuests > 0)
                                            <br>Extra Mattress ({{ $extraGuests }} guest{{ $extraGuests > 1 ? 's' : '' }}): ₱{{ number_format($extraRate, 2) }}/night
                                        @endif
                                        @if($petFee > 0)
                                            <br>Pet Fee ({{ $accommodation['num_pets'] }} pet{{ $accommodation['num_pets'] > 1 ? 's' : '' }}): ₱{{ number_format($petFee, 2) }}
                                        @endif
                                        @if($breakfastFee > 0)
                                            <br>Breakfast ({{ $accommodation['guests'] }} guest{{ $accommodation['guests'] > 1 ? 's' : '' }}): ₱{{ number_format($breakfastFee, 2) }}
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                        <p><strong>Accommodation Total:</strong> ₱{{ number_format($accommodationTotal, 2) }}</p>
                        <p><strong>Activities Total:</strong> ₱{{ number_format($bookingData['final_totals']['activities_total'] ?? 0, 2) }}</p>
                        <p><strong>Total Amount Paid:</strong> ₱{{ number_format($accommodationTotal + ($bookingData['final_totals']['activities_total'] ?? 0), 2) }}</p>
                    @endif
                @else
                    <p><strong>Type:</strong> {{ ucfirst($bookingData['type'] ?? 'N/A') }}</p>
                    <p><strong>Package:</strong> {{ $bookingData['package'] ?? 'N/A' }}</p>
                    <p><strong>Price:</strong> ₱{{ number_format((float)($bookingData['price'] ?? 0)) }}</p>
                    <p><strong>Preferred Date:</strong> {{ !empty($bookingData['booking_date']) ? \Carbon\Carbon::parse($bookingData['booking_date'])->format('F j, Y') : 'N/A' }}</p>
                    <p><strong>Number of Guests:</strong> {{ $bookingData['guests'] ?? 'N/A' }}</p>
                    @php
                    $activitiesTotal = 0;
                    if (!empty($selectedActivities)) {
                        $activitiesTotal = collect($selectedActivities)->sum(function($activity) {
                            return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
                        });
                    }
                    $packageTotal = (float)($bookingData['price'] ?? 0);
                    $grandTotal = $bookingData['grand_total'] ?? ($packageTotal + $activitiesTotal);
                    @endphp

                    @if(!empty($selectedActivities))
                    <p><strong>Selected Activities:</strong></p>
                    <ul>
                        @foreach($selectedActivities as $activity)
                        <li>{{ $activity['name'] }} ({{ $activity['guests'] ?? 1 }} guest{{ ($activity['guests'] ?? 1) > 1 ? 's' : '' }}) - ₱{{ number_format(($activity['price'] ?? 0) * ($activity['guests'] ?? 1)) }}</li>
                        @endforeach
                    </ul>
                    <p><strong>Activities Total:</strong> ₱{{ number_format($activitiesTotal) }}</p>
                    @endif
                    <p><strong>Package Total:</strong> ₱{{ number_format($packageTotal) }}</p>
                    <p><strong>Total Amount Paid:</strong> ₱{{ number_format($grandTotal) }}</p>
                    @if($bookingData['special_requests'] ?? $bookingData['customer_info']['special_requests'] ?? false)
                    <p><strong>Special Requests:</strong> {{ $bookingData['special_requests'] ?? $bookingData['customer_info']['special_requests'] ?? '' }}</p>
                    @endif
                @endif
            </div>

            <div class="booking-details">
                <h3>Payment Details:</h3>
                <p><strong>Transaction ID:</strong> {{ $bookingData['transaction_id'] ?? 'N/A' }}</p>
                <p><strong>Payment Method:</strong> {{ ucfirst($bookingData['payment_method'] ?? 'N/A') }}</p>
                <p><strong>Payment Date:</strong> {{ !empty($bookingData['payment_date']) ? \Carbon\Carbon::parse($bookingData['payment_date'])->format('M d, Y H:i') : 'N/A' }}</p>
                <p><strong>Total Amount Paid:</strong> ₱{{ number_format($grandTotal ?? $bookingData['grand_total'] ?? $bookingData['final_totals']['grand_total'] ?? 0, 2) }}</p>
            </div>

            <h3>What's Next?</h3>
            <ul>
                <li>Keep this receipt for your records.</li>
                <li>Save the date in your calendar. We'll send reminders as your booking date approaches.</li>
                <li>Contact us if you need to make changes or have any questions.</li>
            </ul>

            <p>If you have any questions or need to make changes, please contact us at:</p>
            <p>Email: calaia.ph@gmail.com<br>
            Phone: +63 905 129 4741</p>

            <p>We look forward to welcoming you to CalaiaPH!</p>

            <p>Best regards,<br>
            The CalaiaPH Team</p>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>