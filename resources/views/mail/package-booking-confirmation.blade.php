<x-mail::message>
@php
    $packageName = $package?->name ?? 'Package';
    $confirmationContent = $confirmationContent ?? app(\App\Services\BookingConfirmationContentService::class)->forType('package', [
        'reference' => $booking->booking_reference,
        'package' => $packageName,
    ]);
    $pageContent = $confirmationContent['page'] ?? [];
    $nextStepsContent = $confirmationContent['next_steps'] ?? [];
    $supportContent = $confirmationContent['support'] ?? [];
    $emailContent = $confirmationContent['email'] ?? [];
@endphp

# {{ $pageContent['title'] ?? 'Booking Confirmation' }}

{{ $emailContent['greeting'] ?? 'Hi' }} {{ $booking->first_name }} {{ $booking->last_name }},

{{ $emailContent['intro'] ?? 'Your package booking is confirmed.' }}

## Booking Details

- **Reference:** {{ $booking->booking_reference }}
- **Package:** {{ $packageName }}
- **Booking Date:** {{ $booking->formatted_booking_date }}
- **Booking Time:** {{ $booking->display_session }}
- **Guests:** {{ $booking->total_guests }}
- **Groups Booked:** {{ $booking->groups_booked ?? 1 }}
- **Total Amount:** PHP {{ number_format((float) $booking->total_amount, 2) }}
- **Payment Status:** {{ ucfirst($booking->payment_status ?? 'pending') }}
- **Status:** {{ ucfirst($booking->status ?? 'pending') }}

@if(!empty($booking->special_requests))
## Special Requests

{{ $booking->special_requests }}
@endif

## What's Next?

@forelse(($nextStepsContent['items'] ?? []) as $step)
- {{ $step }}
@empty
- Keep your booking reference for future support.
- Proceed with the payment flow if you haven't completed it yet.
@endforelse

{{ $emailContent['closing'] ?? 'If you need changes to your reservation, contact us with your reference number.' }}

@if(!empty($supportContent['email']) || !empty($supportContent['phone']))
Support:
- Email: {{ $supportContent['email'] ?? 'calaia.ph@gmail.com' }}
- Phone: {{ $supportContent['phone'] ?? '+63 905 129 4741' }}
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
