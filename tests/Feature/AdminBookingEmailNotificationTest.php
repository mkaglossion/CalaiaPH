<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmation;
use App\Mail\PackageBookingConfirmation;
use App\Models\Package;
use App\Models\PackageBooking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminBookingEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staycation_confirmation_is_bcced_only_to_official_admin_email(): void
    {
        Mail::fake();

        Mail::to('guest@example.com')->sendNow(new BookingConfirmation([
            'bookingData' => [
                'transaction_id' => 'TXN-ADMIN-MAIL-1',
                'email' => 'guest@example.com',
            ],
            'customerInfo' => ['email' => 'guest@example.com'],
            'isStaycation' => true,
        ]));

        Mail::assertSent(BookingConfirmation::class, function (BookingConfirmation $mail) {
            return $mail->hasTo('guest@example.com')
                && $mail->hasBcc('calaia.ph@gmail.com');
        });
    }

    public function test_package_confirmation_is_bcced_to_official_admin_email(): void
    {
        $package = Package::create([
            'name' => 'Admin Notification Package',
            'description' => 'Package used to verify admin booking emails.',
            'price' => 2500,
            'max_guests' => 10,
            'pricing_type' => 'fixed',
            'is_active' => true,
        ]);
        $booking = PackageBooking::create([
            'booking_reference' => 'PB-ADMIN-MAIL-1',
            'package_id' => $package->id,
            'booking_date' => now()->addWeek()->toDateString(),
            'total_guests' => 2,
            'first_name' => 'Package',
            'last_name' => 'Guest',
            'email' => 'package-guest@example.com',
            'phone' => '09171234567',
            'total_amount' => 2500,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        Mail::fake();

        Mail::to($booking->email)->sendNow(new PackageBookingConfirmation($booking));

        Mail::assertSent(PackageBookingConfirmation::class, function (PackageBookingConfirmation $mail) {
            return $mail->hasTo('package-guest@example.com')
                && $mail->hasBcc('calaia.ph@gmail.com');
        });
    }

    public function test_non_staycation_confirmation_does_not_notify_admins(): void
    {
        Mail::fake();

        Mail::to('day-guest@example.com')->sendNow(new BookingConfirmation([
            'bookingData' => [
                'transaction_id' => 'TXN-DAY-MAIL-1',
                'email' => 'day-guest@example.com',
            ],
            'customerInfo' => ['email' => 'day-guest@example.com'],
            'isDayvisit' => true,
        ]));

        Mail::assertSent(BookingConfirmation::class, fn (BookingConfirmation $mail) =>
            $mail->hasTo('day-guest@example.com')
            && ! $mail->hasBcc('calaia.ph@gmail.com')
        );
    }

    public function test_official_email_is_not_duplicated_when_it_is_the_customer_email(): void
    {
        Mail::fake();

        Mail::to('calaia.ph@gmail.com')->sendNow(new BookingConfirmation([
            'bookingData' => [
                'transaction_id' => 'TXN-ADMIN-MAIL-2',
                'email' => 'calaia.ph@gmail.com',
            ],
            'customerInfo' => ['email' => 'calaia.ph@gmail.com'],
            'isStaycation' => true,
        ]));

        Mail::assertSent(BookingConfirmation::class, fn (BookingConfirmation $mail) =>
            $mail->hasTo('calaia.ph@gmail.com')
            && ! $mail->hasBcc('calaia.ph@gmail.com')
        );
    }
}
