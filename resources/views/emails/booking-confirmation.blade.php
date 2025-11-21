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
                @php
                $isStaycation = isset($bookingData['accommodations']) && !empty($bookingData['accommodations']);
                @endphp
                @if($isStaycation)
                    <p><strong>Booking Type:</strong> Staycation</p>
                    <p><strong>Check-in:</strong> {{ !empty($bookingData['checkin']) ? \Carbon\Carbon::parse($bookingData['checkin'])->format('M d, Y') : '' }}</p>
                    <p><strong>Check-out:</strong> {{ !empty($bookingData['checkout']) ? \Carbon\Carbon::parse($bookingData['checkout'])->format('M d, Y') : '' }}</p>
                    <p><strong>Nights:</strong> {{ !empty($bookingData['checkin']) && !empty($bookingData['checkout']) ? \Carbon\Carbon::parse($bookingData['checkout'])->diffInDays(\Carbon\Carbon::parse($bookingData['checkin'])) : 0 }}</p>
                    <p><strong>Guests:</strong> {{ collect($bookingData['accommodations'])->sum('guests') }}</p>
                    <p><strong>Name:</strong> {{ ($bookingData['first_name'] ?? $bookingData['customer_info']['first_name'] ?? '') . ' ' . ($bookingData['last_name'] ?? $bookingData['customer_info']['last_name'] ?? '') }}</p>
                    <p><strong>Email:</strong> {{ $bookingData['email'] ?? $bookingData['customer_info']['email'] ?? '' }}</p>
                    @if (!empty($bookingData['accommodations']))
                        <p><strong>Selected Accommodations:</strong></p>
                        <ul>
                            @php
                            $checkin = !empty($bookingData['checkin']) ? \Carbon\Carbon::parse($bookingData['checkin']) : null;
                            $checkout = !empty($bookingData['checkout']) ? \Carbon\Carbon::parse($bookingData['checkout']) : null;
                            $nights = $checkin && $checkout ? $checkout->diffInDays($checkin) : 0;
                            @endphp
                            @foreach ($bookingData['accommodations'] as $accommodation)
                                @if (!empty($accommodation['staycation']))
                                    @php
                                    $quantity = $accommodation['guests'] ?? 1;
                                    $name = strtolower($accommodation['staycation']['name']);
                                    $totalPrice = 0;

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
                                            $totalPrice += $basePrice + ($extraGuests * 400);
                                            $current->modify('+1 day');
                                        }
                                    } elseif ($checkin && $checkout && (str_contains($name, 'bell') || str_contains($name, 'dome'))) {
                                        $totalPrice = 3000 * $nights;
                                    } elseif ($checkin && $checkout && (str_contains($name, 'byot') || str_contains($name, 'car camping'))) {
                                        $totalPrice = 300 * $quantity * $nights;
                                    } else {
                                        $totalPrice = $accommodation['staycation']['price'] * $quantity * $nights;
                                    }
                                    @endphp
                                    <li>{{ $accommodation['staycation']['name'] }} - ₱{{ number_format($totalPrice, 2) }}</li>
                                @endif
                            @endforeach
                        </ul>
                        <p><strong>Accommodation Total:</strong> ₱{{ number_format($bookingData['final_totals']['accommodation_total'] ?? 0, 2) }}</p>
                        <p><strong>Activities Total:</strong> ₱{{ number_format($bookingData['final_totals']['activities_total'] ?? 0, 2) }}</p>
                        <p><strong>Total Amount Paid:</strong> ₱{{ number_format($bookingData['grand_total'] ?? 0, 2) }}</p>
                    @endif
                @else
                    <p><strong>Type:</strong> {{ ucfirst($bookingData['type']) }}</p>
                    <p><strong>Package:</strong> {{ $bookingData['package'] }}</p>
                    <p><strong>Price:</strong> ₱{{ number_format((float)$bookingData['price']) }}</p>
                    @if(!empty($selectedActivities))
                    <p><strong>Selected Activities:</strong></p>
                    <ul>
                        @foreach($selectedActivities as $activity)
                        <li>{{ $activity['name'] }} - ₱{{ number_format($activity['price']) }}</li>
                        @endforeach
                    </ul>
                    <p><strong>Total Price:</strong> ₱{{ number_format((float)$bookingData['price'] + collect($selectedActivities)->sum('price')) }}</p>
                    @endif
                    <p><strong>Preferred Date:</strong> {{ \Carbon\Carbon::parse($bookingData['booking_date'])->format('F j, Y') }}</p>
                    <p><strong>Number of Guests:</strong> {{ $bookingData['guests'] }}</p>
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
                <p><strong>Total Amount Paid:</strong> ₱{{ number_format($bookingData['grand_total'] ?? $bookingData['final_totals']['grand_total'] ?? 0, 2) }}</p>
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