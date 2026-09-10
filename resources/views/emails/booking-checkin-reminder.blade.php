<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming CalaiaPH Check-in</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f3; color: #292524; font-family: Arial, sans-serif; }
        .wrapper { width: 100%; padding: 28px 12px; }
        .email { width: 100%; max-width: 620px; margin: 0 auto; background: #ffffff; border: 1px solid #e7e5e4; }
        .header { padding: 28px 32px; background: #8f5d54; color: #ffffff; }
        .eyebrow { margin: 0 0 8px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .header h1 { margin: 0; font-size: 26px; line-height: 1.25; }
        .content { padding: 30px 32px; }
        .content p { margin: 0 0 16px; line-height: 1.6; }
        .date-box { margin: 22px 0; padding: 20px; background: #f7f2f1; border-left: 4px solid #8f5d54; }
        .date-label { margin: 0 0 6px; color: #78716c; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .date-value { margin: 0; color: #292524; font-size: 22px; font-weight: bold; }
        .details { width: 100%; margin: 20px 0; border-collapse: collapse; }
        .details th, .details td { padding: 11px 0; border-bottom: 1px solid #e7e5e4; text-align: left; vertical-align: top; }
        .details th { width: 38%; color: #78716c; font-size: 13px; font-weight: normal; }
        .details td { color: #292524; font-size: 14px; font-weight: bold; }
        .note { padding: 16px; background: #f5f5f4; color: #57534e; font-size: 13px; line-height: 1.6; }
        .footer { padding: 20px 32px; background: #fafaf9; color: #78716c; font-size: 12px; line-height: 1.5; text-align: center; }
        @media only screen and (max-width: 640px) {
            .wrapper { padding: 0; }
            .header, .content, .footer { padding-left: 20px; padding-right: 20px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="email">
            <div class="header">
                <p class="eyebrow">Upcoming Stay Reminder</p>
                <h1>Your CalaiaPH check-in is coming up</h1>
            </div>

            <div class="content">
                <p>Dear {{ $booking->display_customer_name }},</p>
                <p>This is a friendly reminder that your paid and confirmed stay at CalaiaPH is scheduled soon.</p>

                <div class="date-box">
                    <p class="date-label">Check-in date</p>
                    <p class="date-value">{{ $booking->checkin_date->format('l, F j, Y') }}</p>
                </div>

                <table class="details" role="presentation">
                    <tr>
                        <th>Booking reference</th>
                        <td>{{ $booking->transaction_id }}</td>
                    </tr>
                    <tr>
                        <th>Accommodation</th>
                        <td>{{ $booking->display_type_label }}</td>
                    </tr>
                    @if($booking->checkout_date)
                    <tr>
                        <th>Check-out date</th>
                        <td>{{ $booking->checkout_date->format('l, F j, Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Booking status</th>
                        <td>Paid and confirmed</td>
                    </tr>
                </table>

                <p class="note">Please keep your booking reference available and review your confirmed arrangements before travelling. Contact the CalaiaPH team as soon as possible if any booking detail needs attention.</p>

                <p>We look forward to welcoming you.</p>
                <p>Best regards,<br>The CalaiaPH Team</p>
            </div>

            <div class="footer">
                This automated reminder was sent to {{ $booking->customer_email }} for booking {{ $booking->transaction_id }}.<br>
                Support: {{ config('mail.booking_admin_address', 'calaia.ph@gmail.com') }}
            </div>
        </div>
    </div>
</body>
</html>
