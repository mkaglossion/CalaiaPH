@component('mail::message')
# Booking Confirmation

Thank you for booking with CalaiaPH!

## Booking Details

**Booking Reference:** {{ $booking->booking_reference }}

**Package:** {{ $package->name }}

**Booking Date:** {{ $booking->booking_date->format('F d, Y') }}

**Session:** {{ ucfirst($booking->session) }}

**Number of Guests:** {{ $booking->total_guests }}

---

## Customer Information

**Name:** {{ $booking->full_name }}

**Email:** {{ $booking->email }}

**Phone:** {{ $booking->phone }}

@if($booking->special_requests)
**Special Requests:** 

{{ $booking->special_requests }}
@endif

---

## Payment Summary

**Total Amount:** ₱{{ number_format($booking->total_amount, 2) }}

**Payment Status:** {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}

---

@component('mail::button', ['url' => url('/packages')])
View More Packages
@endcomponent

If you have any questions, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent
