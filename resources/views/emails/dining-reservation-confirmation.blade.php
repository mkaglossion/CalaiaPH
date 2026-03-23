{{-- resources/views/emails/dining-reservation-confirmation.blade.php --}}
@php
    use Carbon\Carbon;

    $guestCount = (int) ($reservation->adults ?? 0);
    $mealPrice = (float) ($food->price ?? 0);
    $subtotal = $mealPrice * $guestCount;
    $vatAmount = $subtotal * 0.12 / 1.12; // VAT already included in total
    $confirmationNumber = 'DR' . str_pad($reservation->id ?? 0, 5, '0', STR_PAD_LEFT);

    $formattedDate = !empty($reservation->reservation_date)
        ? Carbon::parse($reservation->reservation_date)->format('M d, Y')
        : 'N/A';

    $formattedTime = !empty($reservation->reservation_time)
        ? Carbon::parse($reservation->reservation_time)->format('g:i A')
        : 'N/A';

    $firstName = $reservation->first_name ?? 'Guest';
    $mealPeriod = !empty($reservation->meal_period) ? ucfirst($reservation->meal_period) : 'Meal';
    $foodName = $food->name ?? 'Dining Experience';
    $phone = $reservation->phone ?? 'N/A';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmed</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Trebuchet MS', sans-serif;
            line-height: 1.6;
            color: #2d2d2d;
            background-color: #fafafa;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e8e8e8;
        }

        .header {
            background: linear-gradient(135deg, #97655A 0%, #b8826d 100%);
            padding: 40px 20px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            font-size: 32px;
            margin: 0 0 8px;
            font-weight: 700;
            letter-spacing: 2px;
            line-height: 1.2;
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            letter-spacing: 3px;
            margin: 0;
            font-weight: 500;
            text-transform: uppercase;
        }

        .success-section {
            background: linear-gradient(135deg, #f5efe7 0%, #faf7f2 100%);
            padding: 35px 20px;
            text-align: center;
            border-bottom: 1px solid #e8e8e8;
        }

        .success-title {
            color: #97655A;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .success-subtitle {
            color: #666;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.5;
        }

        .content {
            padding: 20px 15px;
        }

        .intro-text {
            font-size: 14px;
            color: #2d2d2d;
            margin-bottom: 15px;
            line-height: 1.7;
        }

        .intro-text strong {
            color: #97655A;
        }

        .details-section,
        .pricing-section {
            background-color: #f8f8f8;
            border: 1px solid #e8e8e8;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .pricing-section {
            padding: 15px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #2d2d2d;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #97655A;
            display: inline-block;
        }

        .detail-item,
        .pricing-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #e8e8e8;
            font-size: 14px;
        }

        .detail-item:last-child,
        .pricing-row:last-child {
            border-bottom: none;
        }

        .detail-label,
        .pricing-label {
            color: #666;
            font-weight: 600;
            flex: 1;
        }

        .detail-value,
        .pricing-value {
            color: #2d2d2d;
            text-align: right;
            flex: 1;
            font-weight: 500;
            word-break: break-word;
        }

        .detail-value.highlight {
            color: #97655A;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            font-size: 15px;
        }

        .pricing-row.total {
            border-top: 2px solid #e8e8e8;
            border-bottom: none;
            padding: 15px 0 10px;
            font-size: 15px;
            font-weight: 700;
        }

        .pricing-row.total .pricing-label {
            color: #2d2d2d;
        }

        .pricing-row.total .pricing-value {
            color: #97655A;
            font-size: 18px;
        }

        .next-steps {
            background-color: #f0f4f8;
            border-left: 4px solid #97655A;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .next-steps-title {
            color: #2d2d2d;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .step-item {
            margin-bottom: 8px;
            color: #2d2d2d;
            font-size: 13px;
            line-height: 1.6;
        }

        .step-item:last-child {
            margin-bottom: 0;
        }

        .step-item::before {
            content: '✓ ';
            color: #97655A;
            font-weight: 700;
            margin-right: 8px;
        }

        .reminders {
            background-color: #fffbf0;
            border-left: 4px solid #e8a646;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            font-size: 13px;
            color: #5c4d2d;
        }

        .reminders strong {
            display: block;
            margin-bottom: 8px;
            color: #2d2d2d;
            font-size: 14px;
        }

        .reminders ul {
            margin: 0;
            padding-left: 18px;
        }

        .reminders li {
            margin-bottom: 6px;
            line-height: 1.5;
        }

        .reminders li:last-child {
            margin-bottom: 0;
        }

        .footer-content {
            padding: 20px 15px;
            text-align: center;
            border-top: 1px solid #e8e8e8;
            background-color: #fafafa;
        }

        .contact-info {
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .contact-info a {
            color: #97655A;
            text-decoration: none;
            font-weight: 600;
        }

        .divider {
            height: 1px;
            background-color: #e8e8e8;
            margin: 18px 0;
        }

        .footer-text {
            font-size: 11px;
            color: #999;
            font-style: italic;
        }

        .vat-note {
            font-size: 11px;
            color: #999;
            font-style: italic;
            text-align: center;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e8e8e8;
        }

        @media only screen and (max-width: 600px) {
            .detail-item,
            .pricing-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .detail-value,
            .pricing-value {
                text-align: left;
            }

            .header h1 {
                font-size: 28px;
            }

            .success-title {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>CALAIA</h1>
            <p>Farm Eco-Resort</p>
        </div>

        {{-- Success Message --}}
        <div class="success-section">
            <div class="success-title">Reservation Confirmed</div>
            <div class="success-subtitle">Your dining experience is booked!</div>
        </div>

        {{-- Main Content --}}
        <div class="content">
            <div class="intro-text">
                Hi <strong>{{ $firstName }}</strong>,<br><br>
                Thank you for booking with us! Your dining reservation has been confirmed and we look forward to welcoming you.
            </div>

            {{-- Reservation Details --}}
            <div class="details-section">
                <div class="section-title">Your Reservation</div>

                <div class="detail-item">
                    <span class="detail-label">Confirmation #</span>
                    <span class="detail-value highlight">{{ $confirmationNumber }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Meal Experience</span>
                    <span class="detail-value">{{ $mealPeriod }} - {{ $foodName }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Date</span>
                    <span class="detail-value">{{ $formattedDate }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Time</span>
                    <span class="detail-value">{{ $formattedTime }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Party Size</span>
                    <span class="detail-value">
                        {{ $guestCount }} Guest{{ $guestCount !== 1 ? 's' : '' }}
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Contact</span>
                    <span class="detail-value">+63 {{ $phone }}</span>
                </div>
            </div>

            {{-- Special Requests --}}
            @if(!empty($reservation->special_requests))
                <div class="details-section">
                    <div class="section-title">Special Notes</div>
                    <p style="color: #2d2d2d; font-size: 14px; line-height: 1.6; margin: 0;">
                        {{ $reservation->special_requests }}
                    </p>
                </div>
            @endif

            {{-- Pricing --}}
            <div class="pricing-section">
                <div class="section-title">Price Breakdown</div>

                <div class="pricing-row">
                    <span class="pricing-label">Meal Price</span>
                    <span class="pricing-value">₱{{ number_format($mealPrice, 2) }}</span>
                </div>

                <div class="pricing-row">
                    <span class="pricing-label">× {{ $guestCount }} Guest{{ $guestCount !== 1 ? 's' : '' }}</span>
                    <span class="pricing-value">₱{{ number_format($subtotal, 2) }}</span>
                </div>

                <div class="pricing-row">
                    <span class="pricing-label">Subtotal</span>
                    <span class="pricing-value">₱{{ number_format($subtotal, 2) }}</span>
                </div>

                <div class="pricing-row">
                    <span class="pricing-label">VAT (12%)</span>
                    <span class="pricing-value">₱{{ number_format($vatAmount, 2) }}</span>
                </div>

                <div class="pricing-row total">
                    <span class="pricing-label">Total</span>
                    <span class="pricing-value">₱{{ number_format($subtotal, 2) }}</span>
                </div>

                <div class="vat-note">
                    All prices include 12% VAT
                </div>
            </div>

            {{-- What's Next --}}
            <div class="next-steps">
                <div class="next-steps-title">What Happens Next</div>
                <div class="step-item">Please arrive 10–15 minutes early to check in</div>
                <div class="step-item">Present this confirmation number at the door</div>
                <div class="step-item">Enjoy your dining experience with us!</div>
            </div>

            {{-- Important Info --}}
            <div class="reminders">
                <strong>Important to Know</strong>
                <ul>
                    <li><strong>Cancellation:</strong> Free cancellations up to 24 hours before your reservation</li>
                    <li><strong>Modifications:</strong> Need to change the date or party size? Contact us as soon as possible</li>
                    <li><strong>Dietary needs:</strong> Special requests noted. We'll do our best to accommodate</li>
                </ul>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer-content">
            <div class="contact-info">
                <strong>Questions?</strong><br>
                <a href="mailto:calaia.ph@gmail.com">calaia.ph@gmail.com</a>
                • (02) 8633 8888
                • +63 905 129 4741
            </div>

            <div class="divider"></div>

            <div class="footer-text">
                <strong style="color: #2d2d2d;">Calaia Farm Eco-Resort</strong><br>
                © {{ now()->year }} All rights reserved.<br>
                We can't wait to welcome you!
            </div>
        </div>
    </div>
</body>
</html>
