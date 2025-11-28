<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - CalaiaPH</title>
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
            <h1>CalaiaPH Receipt</h1>
        </div>

        <div class="content">
            <p>Dear {{ $bookingData['name'] }},</p>

            <p>Your activity booking has been successfully processed. Below is your receipt. Thank you for choosing CalaiaPH!</p>

            <div class="booking-details">
                <h3>Booking Details:</h3>
                <p><strong>Name:</strong> {{ $bookingData['name'] }}</p>
                <p><strong>Email:</strong> {{ $bookingData['email'] }}</p>
                <p><strong>Phone:</strong> {{ $bookingData['phone'] }}</p>
                <p><strong>Address:</strong> {{ $bookingData['address'] }}</p>
                <p><strong>Booking Date:</strong> {{ \Carbon\Carbon::parse($bookingData['booking_date'])->format('F j, Y') }}</p>
                <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $bookingData['payment_method'])) }}</p>

                <h4>Activities:</h4>
                <ul>
                    @foreach($cart as $id => $item)
                    <li>{{ $item['title'] }} (Qty: {{ $item['quantity'] }}) - ₱{{ number_format($item['price'] * $item['quantity'], 2) }}</li>
                    @endforeach
                </ul>

                <p><strong>Total Amount:</strong> ₱{{ number_format($cartTotal, 2) }}</p>
            </div>

            <h3>What's Next?</h3>
            <ul>
                <li>Keep this receipt for your records.</li>
                <li>Save the booking date in your calendar.</li>
                <li>Contact us if you need to make changes or have any questions.</li>
            </ul>

            <p>If you have any questions, please contact us at:</p>
            <p>Email: calaia.ph@gmail.com<br>
            Phone: +63 905 129 4741</p>

            <p>We look forward to seeing you at CalaiaPH!</p>

            <p>Best regards,<br>
            The CalaiaPH Team</p>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>