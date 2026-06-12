<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaycationWaiverTest extends TestCase
{
    use RefreshDatabase;

    public function test_staycation_payment_requires_camp_waiver(): void
    {
        $response = $this->from(route('booking.staycationPayment'))
            ->withSession([
                'booking' => [
                    'booking_type' => 'staycation',
                    'checkin' => '2026-07-01',
                    'checkout' => '2026-07-02',
                    'accommodations' => [],
                    'final_totals' => [
                        'grand_total' => 0,
                    ],
                ],
            ])
            ->post(route('booking.initiatePayment'), [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan@example.com',
                'phone' => '09171234567',
                'payment_method' => 'paymongo',
                'terms_and_policy_accepted' => 'on',
            ]);

        $response->assertRedirect(route('booking.staycationPayment'));
        $response->assertSessionHasErrors(['camp_waiver_accepted']);
    }

    public function test_booking_confirmation_email_shows_accepted_agreements(): void
    {
        $mail = new BookingConfirmation([
            'bookingData' => [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan@example.com',
                'acceptances' => [
                    [
                        'label' => 'Terms & Conditions and Cancellation Policy',
                        'accepted' => true,
                        'accepted_at' => '2026-06-11 14:00:00',
                    ],
                    [
                        'label' => 'Camp Waiver and Release from Liability',
                        'accepted' => true,
                        'accepted_at' => '2026-06-11 14:00:00',
                    ],
                ],
            ],
            'customerInfo' => [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan@example.com',
            ],
            'confirmationContent' => [
                'email' => [
                    'subject' => 'Booking Receipt - CalaiaPH',
                ],
                'next_steps' => [],
                'support' => [],
            ],
            'isStaycation' => true,
        ]);

        $html = $mail->render();

        $this->assertStringContainsString('Accepted Agreements', $html);
        $this->assertStringContainsString('Camp Waiver and Release from Liability', $html);
    }
}
