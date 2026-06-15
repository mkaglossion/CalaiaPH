<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
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
                'transaction_id' => 'TXN-2026-0001',
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
        $this->assertStringContainsString(route('booking.camp-waiver', ['transactionId' => 'TXN-2026-0001']), $html);
        $this->assertStringContainsString(route('booking.camp-waiver.pdf', ['transactionId' => 'TXN-2026-0001']), $html);
        $this->assertCount(1, $mail->attachments());
    }

    public function test_booking_confirmation_shows_camp_waiver_link_for_staycation_bookings(): void
    {
        $response = $this->withSession([
            'booking' => [
                'booking_type' => 'staycation',
                'transaction_id' => 'TXN-2026-0002',
                'checkin' => '2026-07-01',
                'checkout' => '2026-07-02',
                'accommodations' => [],
                'final_totals' => [
                    'grand_total' => 0,
                ],
                'acceptances' => [
                    [
                        'source' => 'camp_waiver_accepted',
                        'label' => 'Camp Waiver and Release from Liability',
                        'accepted' => true,
                    ],
                ],
            ],
            'staycation_customer_info' => [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan@example.com',
            ],
        ])->get(route('booking.confirmation'));

        $response->assertOk();
        $response->assertSee(route('booking.camp-waiver', ['transactionId' => 'TXN-2026-0002']), false);
        $response->assertSee(route('booking.camp-waiver.pdf', ['transactionId' => 'TXN-2026-0002']), false);
    }

    public function test_camp_waiver_page_renders_from_confirmed_booking(): void
    {
        Booking::create([
            'transaction_id' => 'TXN-2026-0003',
            'customer_name' => 'Juan Dela Cruz',
            'customer_email' => 'juan@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => '2026-07-01',
            'checkout_date' => '2026-07-02',
            'booking_data' => [
                'package' => 'Staycation',
                'customer_info' => [
                    'first_name' => 'Juan',
                    'last_name' => 'Dela Cruz',
                    'email' => 'juan@example.com',
                    'phone' => '09171234567',
                ],
                'acceptances' => [
                    [
                        'source' => 'camp_waiver_accepted',
                        'label' => 'Camp Waiver and Release from Liability',
                        'accepted' => true,
                        'accepted_at' => '2026-06-11 14:00:00',
                        'details' => [
                            'participant_name' => 'Juan Dela Cruz',
                            'signature_name' => 'Juan Dela Cruz',
                            'signature_date' => '2026-06-11',
                        ],
                    ],
                ],
            ],
            'total_amount' => 0,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        $response = $this->get(route('booking.camp-waiver', ['transactionId' => 'TXN-2026-0003']));

        $response->assertOk();
        $response->assertSee('Camp Waiver and Release from Liability');
        $response->assertSee('Print Camp Waiver');
    }

    public function test_camp_waiver_pdf_downloads_from_confirmed_booking(): void
    {
        Booking::create([
            'transaction_id' => 'TXN-2026-0004',
            'customer_name' => 'Juan Dela Cruz',
            'customer_email' => 'juan@example.com',
            'customer_phone' => '09171234567',
            'booking_type' => 'staycation',
            'checkin_date' => '2026-07-01',
            'checkout_date' => '2026-07-02',
            'booking_data' => [
                'package' => 'Staycation',
                'customer_info' => [
                    'first_name' => 'Juan',
                    'last_name' => 'Dela Cruz',
                    'email' => 'juan@example.com',
                    'phone' => '09171234567',
                ],
                'acceptances' => [
                    [
                        'source' => 'camp_waiver_accepted',
                        'label' => 'Camp Waiver and Release from Liability',
                        'accepted' => true,
                        'accepted_at' => '2026-06-11 14:00:00',
                        'details' => [
                            'participant_name' => 'Juan Dela Cruz',
                            'signature_name' => 'Juan Dela Cruz',
                            'signature_date' => '2026-06-11',
                        ],
                    ],
                ],
            ],
            'total_amount' => 0,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        $response = $this->get(route('booking.camp-waiver.pdf', ['transactionId' => 'TXN-2026-0004']));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }
}
