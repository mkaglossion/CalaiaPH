<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Event Booking Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #8f5d54; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .booking-details { background-color: white; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .event-highlight { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CalaiaPH Special Event Booking Confirmation</h1>
        </div>

        <div class="content">
            <p>Dear {{ $bookingData['first_name'] ?? $bookingData['customer_info']['first_name'] ?? 'Guest' }} {{ $bookingData['last_name'] ?? $bookingData['customer_info']['last_name'] ?? '' }},</p>

            <p>Congratulations! Your special event booking has been successfully confirmed. We're excited to host your memorable event at CalaiaPH!</p>

            <div class="event-highlight">
                <h3>🎉 Your Special Event Details</h3>
                <p><strong>Event:</strong> {{ $bookingData['event_name'] ?? 'Special Event' }}</p>
                <p><strong>Event Dates:</strong> {{ !empty($bookingData['checkin']) ? \Carbon\Carbon::parse($bookingData['checkin'])->format('M d, Y') : '' }} - {{ !empty($bookingData['checkout']) ? \Carbon\Carbon::parse($bookingData['checkout'])->format('M d, Y') : '' }}</p>
                <p><strong>Number of Guests:</strong> {{ $bookingData['guests'] }}</p>
            </div>

            <div class="booking-details">
                <h3>Booking Information:</h3>
                <p><strong>Name:</strong> {{ ($bookingData['first_name'] ?? $bookingData['customer_info']['first_name'] ?? '') . ' ' . ($bookingData['last_name'] ?? $bookingData['customer_info']['last_name'] ?? '') }}</p>
                <p><strong>Email:</strong> {{ $bookingData['email'] ?? $bookingData['customer_info']['email'] ?? '' }}</p>
                <p><strong>Phone:</strong> {{ $bookingData['phone'] ?? $bookingData['customer_info']['phone'] ?? '' }}</p>

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

                <p><strong>Base Event Package:</strong> ₱{{ number_format($packageTotal, 2) }}</p>

                @if(!empty($selectedActivities))
                <p><strong>Additional Activities:</strong></p>
                <ul>
                    @foreach($selectedActivities as $activity)
                    <li>{{ $activity['name'] }} ({{ $activity['guests'] ?? 1 }} guest{{ ($activity['guests'] ?? 1) > 1 ? 's' : '' }}) - ₱{{ number_format(($activity['price'] ?? 0) * ($activity['guests'] ?? 1)) }}</li>
                    @endforeach
                </ul>
                <p><strong>Activities Total:</strong> ₱{{ number_format($activitiesTotal) }}</p>
                @endif

                <p><strong>Total Amount:</strong> ₱{{ number_format($grandTotal) }}</p>

                @if($bookingData['special_requests'] ?? $bookingData['customer_info']['special_requests'] ?? false)
                <p><strong>Special Requests:</strong> {{ $bookingData['special_requests'] ?? $bookingData['customer_info']['special_requests'] ?? '' }}</p>
                @endif
            </div>

            <div class="booking-details">
                <h3>Payment Details:</h3>
                <p><strong>Transaction ID:</strong> {{ $bookingData['transaction_id'] ?? 'N/A' }}</p>
                <p><strong>Payment Method:</strong> {{ ucfirst($bookingData['payment_method'] ?? 'N/A') }}</p>
                <p><strong>Payment Date:</strong> {{ !empty($bookingData['payment_date']) ? \Carbon\Carbon::parse($bookingData['payment_date'])->format('M d, Y H:i') : 'N/A' }}</p>
            </div>

            <h3>What to Expect Next:</h3>
            <ul>
                <li>Our event coordinator will contact you within 24-48 hours to discuss event details and customization options.</li>
                <li>You'll receive a detailed event itinerary and timeline closer to your event date.</li>
                <li>Keep this confirmation email for your records and reference.</li>
                <li>Feel free to contact us if you have any questions or need to make changes.</li>
            </ul>

            <p>For any inquiries or assistance, please reach out to our team:</p>
            <p>Email: calaia.ph@gmail.com<br>
            Phone: +63 905 129 4741</p>

            <p>We can't wait to make your special event unforgettable at CalaiaPH!</p>

            <p>Best regards,<br>
            The CalaiaPH Events Team</p>
        </div>

        <div class="footer">
            <p>This is an automated confirmation. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>