<?php

namespace Tests\Feature;

use App\Mail\BookingCheckinReminder;
use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendBookingCheckinRemindersCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_command_emails_customer_and_admin_once_for_tomorrows_paid_confirmed_booking(): void
    {
        CarbonImmutable::setTestNow('2026-09-10 09:00:00');
        config([
            'app.timezone' => 'Asia/Manila',
            'booking.checkin_reminder.days_before' => 1,
            'mail.booking_admin_address' => 'admin@calaia.test',
        ]);
        Mail::fake();

        $booking = Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-REMINDER-1',
            'checkin_date' => '2026-09-11',
            'checkout_date' => '2026-09-12',
        ]));

        $this->artisan('bookings:send-checkin-reminders')
            ->expectsOutput('Sent 1 check-in reminder email(s) for 2026-09-11.')
            ->assertSuccessful();

        Mail::assertSent(BookingCheckinReminder::class, function (BookingCheckinReminder $mail) use ($booking) {
            return $mail->booking->is($booking)
                && $mail->hasTo('guest@example.com')
                && $mail->hasBcc('admin@calaia.test');
        });

        $booking->refresh();
        $this->assertNotNull($booking->checkin_reminder_sent_at);

        $html = (new BookingCheckinReminder($booking))->render();
        $this->assertStringContainsString('Friday, September 11, 2026', $html);
        $this->assertStringContainsString('TXN-REMINDER-1', $html);
        $this->assertStringContainsString('Premium Glamping', $html);

        $this->artisan('bookings:send-checkin-reminders')
            ->expectsOutput('Sent 0 check-in reminder email(s) for 2026-09-11.')
            ->assertSuccessful();

        Mail::assertSentCount(1);
    }

    public function test_command_excludes_ineligible_and_already_reminded_bookings(): void
    {
        CarbonImmutable::setTestNow('2026-09-10 09:00:00');
        config([
            'app.timezone' => 'Asia/Manila',
            'booking.checkin_reminder.days_before' => 1,
        ]);
        Mail::fake();

        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-REMINDER-UNPAID',
            'checkin_date' => '2026-09-11',
            'payment_status' => 'pending',
        ]));
        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-REMINDER-CANCELLED',
            'checkin_date' => '2026-09-11',
            'status' => 'cancelled',
        ]));
        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-REMINDER-ALREADY-SENT',
            'checkin_date' => '2026-09-11',
            'checkin_reminder_sent_at' => now()->subHour(),
        ]));
        Booking::create($this->bookingAttributes([
            'transaction_id' => 'TXN-REMINDER-WRONG-DATE',
            'checkin_date' => '2026-09-12',
        ]));

        $this->artisan('bookings:send-checkin-reminders')
            ->expectsOutput('Sent 0 check-in reminder email(s) for 2026-09-11.')
            ->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_command_rejects_an_invalid_manual_date(): void
    {
        $this->artisan('bookings:send-checkin-reminders', ['--date' => '09/11/2026'])
            ->expectsOutput('The --date option must use a valid YYYY-MM-DD date.')
            ->assertFailed();
    }

    private function bookingAttributes(array $overrides = []): array
    {
        return array_merge([
            'transaction_id' => 'TXN-REMINDER-' . uniqid(),
            'customer_name' => 'Reminder Guest',
            'customer_email' => 'guest@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => '2026-09-11',
            'checkout_date' => '2026-09-12',
            'booking_data' => [
                'accommodations' => [[
                    'name' => 'Premium Glamping',
                ]],
            ],
            'total_amount' => 5000,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ], $overrides);
    }
}
