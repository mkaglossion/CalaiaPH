<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
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
            <h1>CalaiaPH Booking Confirmation</h1>
        </div>

        <div class="content">
            <p>Dear {{ $bookingData['first_name'] }} {{ $bookingData['last_name'] }},</p>

            <p>Thank you for your booking request! We have received your reservation details and will contact you within 24 hours to confirm availability and process your booking.</p>

            <div class="booking-details">
                <h3>Booking Details:</h3>
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
                @if($bookingData['special_requests'])
                <p><strong>Special Requests:</strong> {{ $bookingData['special_requests'] }}</p>
                @endif
            </div>

            <p>If you have any questions or need to make changes, please contact us at:</p>
            <p>Email: info@calaia.ph<br>
            Phone: +63 XXX XXX XXXX</p>

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