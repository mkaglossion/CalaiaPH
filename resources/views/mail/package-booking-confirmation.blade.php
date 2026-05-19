<x-mail::message>
# Booking Confirmation

Hi {{ $booking->first_name }} {{ $booking->last_name }},

Your package booking is confirmed.

## Booking Details

- **Reference:** {{ $booking->booking_reference }}
- **Package:** {{ $package->name ?? 'Package' }}
- **Date:** {{ optional($booking->booking_date)->format('F d, Y') }}
- **Session:** {{ $booking->session ?: 'N/A' }}
- **Guests:** {{ $booking->total_guests }}
- **Groups Booked:** {{ $booking->groups_booked ?? 1 }}
- **Total Amount:** PHP {{ number_format((float) $booking->total_amount, 2) }}
- **Payment Status:** {{ ucfirst($booking->payment_status ?? 'pending') }}
- **Status:** {{ ucfirst($booking->status ?? 'pending') }}

@if(!empty($booking->special_requests))
## Special Requests

{{ $booking->special_requests }}
@endif

If you need changes to your reservation, contact us with your reference number.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
