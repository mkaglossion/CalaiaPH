<!DOCTYPE html>
<!-- Updated to fix undefined variable -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt</title>
    @php
    $staycations = $staycations ?? collect();
    @endphp
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
            <p>Dear {{ $customerInfo['first_name'] ?? $bookingData['first_name'] ?? 'Guest' }} {{ $customerInfo['last_name'] ?? $bookingData['last_name'] ?? '' }},</p>

            <p>Your booking has been successfully processed and confirmed. Below is your receipt. Thank you for choosing CalaiaPH!</p>

            <div class="booking-details">
                <h3>Booking Details:</h3>
                <p><strong>Name:</strong> {{ ($customerInfo['first_name'] ?? $bookingData['first_name'] ?? '') . ' ' . ($customerInfo['last_name'] ?? $bookingData['last_name'] ?? '') }}</p>
                <p><strong>Email:</strong> {{ $customerInfo['email'] ?? $bookingData['email'] ?? '' }}</p>
                @php
                // Use the passed isStaycation flag, default to false if not set
                if (!isset($isStaycation)) {
                    $isStaycation = false;
                }
                // Ensure staycations is defined
                if (!isset($staycations)) {
                    $staycations = collect();
                }
                @endphp
                
                @if($isStaycation)
                    <p><strong>Booking Type:</strong> Staycation</p>
                    <p><strong>Check-in:</strong> {{ !empty($bookingData['checkin']) ? \Carbon\Carbon::parse($bookingData['checkin'])->format('M d, Y') : 'N/A' }}</p>
                    <p><strong>Check-out:</strong> {{ !empty($bookingData['checkout']) ? \Carbon\Carbon::parse($bookingData['checkout'])->format('M d, Y') : 'N/A' }}</p>
                    <p><strong>Nights:</strong> {{ $bookingData['nights'] ?? 'N/A' }}</p>
                    @php
                        $allAreSpecialRooms = collect($bookingData['accommodations'] ?? [])->every(function($acc) {
                            if (empty($acc)) return true;
                            $accommodationId = $acc['room_id'] ?? $acc['accommodation_id'] ?? null;
                            $staycation = ($staycations ?? collect())->get($accommodationId);
                            $name = $staycation->name ?? ($acc['accommodation_name'] ?? '');
                            return stripos($name, 'byot') !== false || stripos($name, 'car camping') !== false || stripos($name, 'bring your own') !== false;
                        });
                    @endphp
                    @if (!$allAreSpecialRooms)
                    <p><strong>Guests:</strong> {{ $bookingData['total_guests'] ?? 'N/A' }}</p>
                    @endif
                    
                    @if (!empty($bookingData['accommodations']))
                        <p><strong>Selected Accommodations:</strong></p>
                        <ul>
                            @foreach ($bookingData['accommodations'] as $roomId => $accommodation)
                                @if (!empty($accommodation))
                                    @php
                                    $staycation = ($staycations ?? collect())->get($accommodation['room_id'] ?? $accommodation['accommodation_id'] ?? $roomId);
                                    $adultsCount = $accommodation['adults'] ?? 0;
                                    $childrenCount = $accommodation['children'] ?? 0;
                                    $quantity = $accommodation['quantity'] ?? 1;
                                    $basePrice = $accommodation['base_price'] ?? 0;
                                    $mattressPrice = $accommodation['mattress_price'] ?? 0;
                                    $petPrice = $accommodation['pet_price'] ?? 0;
                                    $totalPrice = $accommodation['total_price'] ?? ($basePrice + $mattressPrice + $petPrice);
                                    $roomName = $staycation->name ?? '';
                                    $isSpecialRoom = stripos($roomName, 'byot') !== false || stripos($roomName, 'car camping') !== false || stripos($roomName, 'bring your own') !== false;
                                    @endphp
                                    @if($staycation)
                                        <li>
                                            {{ $staycation->name }}
                                            @if($quantity > 1)
                                                - {{ $quantity }} units
                                            @endif
                                            @if(!$isSpecialRoom)
                                            ({{ $adultsCount }} adult{{ $adultsCount !== 1 ? 's' : '' }}, {{ $childrenCount }} child{{ $childrenCount !== 1 ? 'ren' : '' }})
                                            @endif
                                            - ₱{{ number_format($basePrice, 2) }}
                                            @if($mattressPrice > 0)
                                                + Mattress: ₱{{ number_format($mattressPrice, 2) }}
                                            @endif
                                            @if($petPrice > 0)
                                                + Pet: ₱{{ number_format($petPrice, 2) }}
                                            @endif
                                        </li>
                                    @endif
                                @endif
                            @endforeach
                        </ul>
                    @endif
                    
                    @if(!empty($selectedFoods))
                        <p><strong>Selected Foods:</strong></p>
                        <ul>
                            @foreach($selectedFoods as $foodData)
                                @php
                                $foodName = $foodData['name'] ?? 'Unknown Food';
                                $foodPrice = $foodData['price'] ?? 0;
                                $foodQuantity = $foodData['quantity'] ?? 1;
                                $foodItemTotal = $foodPrice * $foodQuantity;
                                @endphp
                                <li>{{ $foodName }} - ₱{{ number_format($foodPrice, 2) }} × {{ $foodQuantity }} = ₱{{ number_format($foodItemTotal, 2) }}</li>
                            @endforeach
                        </ul>
                    @endif
                    
                    @php
                    $accommodationTotal = $bookingData['final_totals']['accommodation_total'] ?? 0;
                    $foodTotal = $bookingData['final_totals']['food_total'] ?? 0;
                    $activitiesTotal = $bookingData['final_totals']['activities_total'] ?? 0;
                    $grandTotal = $bookingData['final_totals']['grand_total'] ?? 0;
                    @endphp
                    
                    @if($accommodationTotal > 0)
                        <p><strong>Accommodation Total:</strong> ₱{{ number_format($accommodationTotal, 2) }}</p>
                    @endif
                    @if($foodTotal > 0)
                        <p><strong>Food Total:</strong> ₱{{ number_format($foodTotal, 2) }}</p>
                    @endif
                    @if($activitiesTotal > 0)
                        <p><strong>Activities Total:</strong> ₱{{ number_format($activitiesTotal, 2) }}</p>
                    @endif
                    @if($grandTotal > 0)
                    <p><strong>Total Amount Paid:</strong> ₱{{ number_format($grandTotal, 2) }}</p>
                    @endif
                @else
                    @php
                    $accommodationTotal = 0;
                    $foodTotal = 0;
                    $activitiesTotal = 0;
                    $grandTotal = $bookingData['price'] ?? 0;
                    @endphp
                    <p><strong>Type:</strong> {{ ucfirst($bookingData['type'] ?? 'N/A') }}</p>
                    <p><strong>Package:</strong> {{ $bookingData['package'] ?? 'N/A' }}</p>
                    <p><strong>Price:</strong> ₱{{ number_format((float)($bookingData['price'] ?? 0)) }}</p>
                    <p><strong>Preferred Date:</strong> {{ !empty($bookingData['booking_date']) ? \Carbon\Carbon::parse($bookingData['booking_date'])->format('F j, Y') : 'N/A' }}</p>
                    <p><strong>Number of Guests:</strong> {{ $bookingData['guests'] ?? 'N/A' }}</p>

                    @if(!empty($selectedActivities))
                    <p><strong>Selected Activities:</strong></p>
                    <ul>
                        @foreach($selectedActivities as $activity)
                        @php
                            $isByotOrCamping = stripos($activity['name'] ?? '', 'byot') !== false || 
                                              stripos($activity['name'] ?? '', 'car camping') !== false || 
                                              stripos($activity['name'] ?? '', 'bring your own') !== false;
                        @endphp
                        <li>
                            {{ $activity['name'] }}
                            @if(!$isByotOrCamping)
                                ({{ $activity['guests'] ?? 1 }} guest{{ ($activity['guests'] ?? 1) > 1 ? 's' : '' }})
                            @endif
                            - ₱{{ number_format(($activity['price'] ?? 0) * ($activity['guests'] ?? 1)) }}
                        </li>
                        @endforeach
                    </ul>
                    @php
                    $activitiesTotal = collect($selectedActivities)->sum(function($activity) {
                        return ($activity['price'] ?? 0) * ($activity['guests'] ?? 1);
                    });
                    @endphp
                    @if($activitiesTotal > 0)
                    <p><strong>Activities Total:</strong> ₱{{ number_format($activitiesTotal, 2) }}</p>
                    @endif
                    @endif
                    @php
                    $grandTotal = ($bookingData['price'] ?? 0) + $activitiesTotal;
                    @endphp
                    @if($grandTotal > 0)
                    <p><strong>Total Amount Paid:</strong> ₱{{ number_format($grandTotal, 2) }}</p>
                    @endif
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
                @php
                // Calculate grand total properly
                if (isset($bookingData['final_totals'])) {
                    $finalGrandTotal = $bookingData['final_totals']['grand_total'] ?? 0;
                } else {
                    $finalGrandTotal = $grandTotal ?? $bookingData['price'] ?? 0;
                }
                @endphp
                <p><strong>Total Amount Paid:</strong> ₱{{ number_format($finalGrandTotal, 2) }}</p>
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