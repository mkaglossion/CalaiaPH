<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Staycation;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaycationWaiverTest extends TestCase
{
    use RefreshDatabase;

    public function test_staycation_payment_requires_camp_waiver(): void
    {
        $response = $this->postWithCsrf(
            route('booking.initiatePayment'),
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan@example.com',
                'phone' => '09171234567',
                'payment_method' => 'paymongo',
                'terms_and_policy_accepted' => 'on',
            ],
            [
                'booking' => [
                    'booking_type' => 'staycation',
                    'checkin' => '2026-07-01',
                    'checkout' => '2026-07-02',
                    'accommodations' => [],
                    'final_totals' => [
                        'grand_total' => 0,
                    ],
                ],
            ],
            route('booking.staycationPayment')
        );

        $response->assertRedirect(route('booking.staycationPayment'));
        $response->assertSessionHasErrors(['camp_waiver_accepted']);
    }

    public function test_complete_staycation_payment_redirects_to_paymongo_checkout(): void
    {
        config()->set('services.paymongo.secret_key', 'sk_test_123');
        Storage::fake('local');

        $this->partialMock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->andReturn((object) [
                    'checkout_url' => 'https://paymongo.test/checkout/cs_test_123',
                    'id' => 'cs_test_123',
                ]);
        });

        $response = $this->postWithCsrf(
            route('booking.initiatePayment'),
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan@example.com',
                'phone' => '09171234567',
                'payment_method' => 'paymongo',
                'terms_and_policy_accepted' => 'on',
                'camp_waiver_accepted' => 'on',
                'guests' => [
                    [
                        'full_name' => 'Juan Dela Cruz',
                        'guest_type' => 'adult',
                        'birth_date' => '1995-05-01',
                        'gender' => 'male',
                        'contact_number' => '09171234567',
                        'emergency_contact_name' => 'Maria Dela Cruz',
                        'emergency_contact_number' => '09170000000',
                        'emergency_contact_relationship' => 'Sibling',
                        'signature_data' => $this->signatureData(),
                    ],
                ],
                'cancellation_policy' => 'flexible',
            ],
            [
                'booking' => [
                    'booking_type' => 'staycation',
                    'checkin' => '2026-07-01',
                    'checkout' => '2026-07-02',
                    'accommodations' => [],
                    'selected_foods' => [],
                    'selected_activities' => [],
                    'selected_activity_packages' => [],
                    'final_totals' => [
                        'grand_total' => 0,
                    ],
                ],
            ]
        );

        $response->assertRedirect('https://paymongo.test/checkout/cs_test_123');
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseHas('bookings', [
            'booking_type' => 'staycation',
            'payment_method' => 'paymongo',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
        $booking = Booking::sole();
        $this->assertDatabaseHas('booking_guests', [
            'booking_id' => $booking->id,
            'full_name' => 'Juan Dela Cruz',
            'guest_type' => 'adult',
            'is_primary_contact' => true,
        ]);
        $this->assertTrue(Storage::disk('local')->exists("signatures/bookings/{$booking->id}/guest-1.png"));
    }

    public function test_checkout_uses_every_guest_from_the_selected_accommodation(): void
    {
        config()->set('services.paymongo.secret_key', 'sk_test_123');
        Storage::fake('local');

        $this->partialMock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->andReturn((object) [
                    'checkout_url' => 'https://paymongo.test/checkout/cs_six_guests',
                    'id' => 'cs_six_guests',
                ]);
        });

        $response = $this->postWithCsrf(
            route('booking.initiatePayment'),
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan@example.com',
                'phone' => '09171234567',
                'payment_method' => 'paymongo',
                'terms_and_policy_accepted' => 'on',
                'camp_waiver_accepted' => 'on',
                'guests' => $this->guestPayloads(4, 2),
                'cancellation_policy' => 'flexible',
            ],
            [
                'booking' => [
                    'booking_type' => 'staycation',
                    'checkin' => '2026-07-01',
                    'checkout' => '2026-07-02',
                    'adults_total' => 1,
                    'children_total' => 0,
                    'accommodations' => [
                        [
                            'room_name' => 'Standard Glamping',
                            'adults' => 4,
                            'children' => 2,
                            'total_price' => 0,
                        ],
                    ],
                    'selected_foods' => [],
                    'selected_activities' => [],
                    'selected_activity_packages' => [],
                    'final_totals' => ['grand_total' => 0],
                ],
            ]
        );

        $response->assertRedirect('https://paymongo.test/checkout/cs_six_guests');
        $booking = Booking::sole();
        $this->assertDatabaseCount('booking_guests', 6);
        $this->assertSame(4, $booking->bookingGuests()->where('guest_type', 'adult')->count());
        $this->assertSame(2, $booking->bookingGuests()->where('guest_type', 'child')->count());
        $this->assertTrue(Storage::disk('local')->exists("signatures/bookings/{$booking->id}/guest-1.png"));
        $this->assertTrue(Storage::disk('local')->exists("signatures/bookings/{$booking->id}/guest-6.png"));
    }

    public function test_checkout_page_hides_guest_details_and_waiver_workflow(): void
    {
        $response = $this->withSession([
            'booking' => [
                'booking_type' => 'staycation',
                'checkin' => '2026-07-01',
                'checkout' => '2026-07-02',
                'adults_total' => 1,
                'children_total' => 0,
                'accommodations' => [
                    [
                        'room_name' => 'Standard Glamping',
                        'adults' => 4,
                        'children' => 2,
                        'total_price' => 0,
                    ],
                ],
                'selected_foods' => [],
                'selected_activities' => [],
                'selected_activity_packages' => [],
                'final_totals' => ['grand_total' => 0],
            ],
        ])->get(route('booking.staycationPayment'));

        $response->assertOk();
        $response->assertDontSee('Guest details and waivers');
        $response->assertDontSee('data-guest-card-index="');
        $response->assertDontSee('Guest 4');
        $response->assertDontSee('Child details and guardian e-signature');
        $response->assertSee('skip_guest_details', false);
        $response->assertSee('I agree to the Terms & Conditions and acknowledge the selected Cancellation Policy', false);
        $response->assertSee('I agree to the Camp Waiver and Release from Liability', false);
        $response->assertSee('Read full booking policy', false);
        $response->assertSee('Review camp waiver', false);
    }

    public function test_room_selection_preserves_separate_adult_and_child_counts(): void
    {
        $room = Staycation::create([
            'name' => 'Standard Glamping',
            'days' => 1,
            'type' => 'glamping',
            'base_quantity' => 3,
            'capacity' => 4,
            'included_guest_capacity' => 4,
            'max_guest_capacity' => 6,
            'adults' => 4,
            'children' => 2,
            'price' => 6800,
        ]);

        $response = $this->withSession([
            'booking' => [
                'booking_type' => 'staycation',
                'checkin' => '2026-07-20',
                'checkout' => '2026-07-21',
                'adults_total' => 6,
                'children_total' => 2,
                'accommodations' => [],
            ],
        ])->postJson(route('booking.staycation'), [
            'selected_rooms' => [
                (string) $room->id => [
                    'id' => $room->id,
                    'adults' => 6,
                    'children' => 2,
                    'extra_mattress' => 2,
                    'pets' => 'no',
                    'pet_agreement' => false,
                    'num_pets' => 0,
                ],
            ],
            'proceed_to_activities' => true,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $booking = session('booking');
        $accommodation = array_values($booking['accommodations'])[0];

        $this->assertSame(6, $accommodation['adults']);
        $this->assertSame(2, $accommodation['children']);
        $this->assertSame(2, $accommodation['additional_adults']);
        $this->assertSame(2400.0, (float) $accommodation['additional_adult_price']);
        $this->assertSame(9200.0, (float) $accommodation['total_price']);
        $this->assertSame(6, $booking['adults_total']);
        $this->assertSame(2, $booking['children_total']);

        $paymentPage = $this->get(route('booking.staycationPayment'));
        $paymentPage->assertOk();
        $paymentPage->assertDontSee('data-guest-card-index="');
        $paymentPage->assertDontSee('Guest details and waivers');
        $paymentPage->assertSee('skip_guest_details', false);
        $paymentPage->assertSee('I agree to the Terms & Conditions and acknowledge the selected Cancellation Policy', false);
        $paymentPage->assertSee('I agree to the Camp Waiver and Release from Liability', false);
    }

    public function test_room_selection_rejects_counts_above_the_configured_child_limit(): void
    {
        $room = Staycation::create([
            'name' => 'Standard Glamping',
            'days' => 1,
            'type' => 'glamping',
            'base_quantity' => 3,
            'capacity' => 4,
            'included_guest_capacity' => 4,
            'max_guest_capacity' => 6,
            'adults' => 4,
            'children' => 2,
            'price' => 6800,
        ]);

        $response = $this->withSession([
            'booking' => [
                'booking_type' => 'staycation',
                'checkin' => '2026-07-20',
                'checkout' => '2026-07-21',
                'accommodations' => [],
            ],
        ])->postJson(route('booking.staycation'), [
            'selected_rooms' => [
                (string) $room->id => [
                    'id' => $room->id,
                    'adults' => 6,
                    'children' => 3,
                    'extra_mattress' => 2,
                    'pets' => 'no',
                    'pet_agreement' => false,
                    'num_pets' => 0,
                ],
            ],
            'proceed_to_activities' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonFragment(['message' => 'Guest allocation exceeds the allowed adult or child count for Standard Glamping. Please review your guest numbers.']);
        $this->assertSame([], session('booking.accommodations'));
    }

    public function test_checkout_rejects_a_missing_guest_card(): void
    {
        $guests = $this->guestPayloads(4, 2);
        array_pop($guests);

        $response = $this->postWithCsrf(
            route('booking.initiatePayment'),
            $this->checkoutPayload($guests),
            ['booking' => $this->sixGuestBooking()],
            route('booking.staycationPayment')
        );

        $response->assertRedirect(route('booking.staycationPayment'));
        $response->assertSessionHasErrors(['guests']);
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('booking_guests', 0);
    }

    public function test_checkout_rejects_a_missing_guest_signature(): void
    {
        $guests = $this->guestPayloads(4, 2);
        unset($guests[5]['signature_data']);

        $response = $this->postWithCsrf(
            route('booking.initiatePayment'),
            $this->checkoutPayload($guests),
            ['booking' => $this->sixGuestBooking()],
            route('booking.staycationPayment')
        );

        $response->assertRedirect(route('booking.staycationPayment'));
        $response->assertSessionHasErrors(['guests.5.signature_data']);
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('booking_guests', 0);
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

        $pdfText = $this->extractPdfText($response->getContent());

        $this->assertStringContainsString('TXN-2026-0004', $pdfText);
        $this->assertStringContainsString('Juan Dela Cruz', $pdfText);
        $this->assertStringContainsString('juan@example.com', $pdfText);
        $this->assertStringContainsString('IV. MEDICAL AUTHORIZATION', $pdfText);
        $this->assertStringNotContainsString('Waiver Text Continued', $pdfText);
    }

    public function test_camp_waiver_preview_transaction_renders_without_404(): void
    {
        $response = $this->get(route('booking.camp-waiver', ['transactionId' => 'PREVIEW-STAY-0001']));

        $response->assertOk();
        $response->assertSee('Camp Waiver and Release from Liability');
        $response->assertSee('Print Camp Waiver');
    }

    public function test_camp_waiver_preview_pdf_downloads_without_404(): void
    {
        $response = $this->get(route('booking.camp-waiver.pdf', ['transactionId' => 'PREVIEW-STAY-0001']));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    private function extractPdfText(string $pdf): string
    {
        preg_match_all('/stream\r?\n(.*?)endstream/s', $pdf, $matches);

        $text = '';

        foreach ($matches[1] as $stream) {
            $decoded = null;

            foreach (['gzuncompress', 'gzinflate', 'gzdecode'] as $decoder) {
                if (!function_exists($decoder)) {
                    continue;
                }

                try {
                    $decoded = $decoder($stream);
                    if ($decoded !== false && $decoded !== null) {
                        break;
                    }
                } catch (\Throwable $e) {
                    $decoded = null;
                }
            }

            if ($decoded === null || $decoded === false) {
                continue;
            }

            $text .= str_replace("\0", '', (string) $decoded) . "\n";
        }

        return $text;
    }

    private function guestPayloads(int $adultCount, int $childCount): array
    {
        $guests = [];

        for ($adult = 1; $adult <= $adultCount; $adult++) {
            $guests[] = [
                'full_name' => "Adult Guest {$adult}",
                'guest_type' => 'adult',
                'birth_date' => '1990-05-01',
                'gender' => 'male',
                'contact_number' => "0917000000{$adult}",
                'emergency_contact_name' => 'Emergency Contact',
                'emergency_contact_number' => '09179999999',
                'emergency_contact_relationship' => 'Sibling',
                'signature_data' => $this->signatureData(),
            ];
        }

        for ($child = 1; $child <= $childCount; $child++) {
            $guests[] = [
                'full_name' => "Child Guest {$child}",
                'guest_type' => 'child',
                'birth_date' => '2018-05-01',
                'gender' => 'female',
                'emergency_contact_name' => 'Juan Dela Cruz',
                'emergency_contact_number' => '09171234567',
                'emergency_contact_relationship' => 'Parent',
                'guardian_full_name' => 'Juan Dela Cruz',
                'guardian_relationship' => 'Father',
                'signature_data' => $this->signatureData(),
            ];
        }

        return $guests;
    }

    private function checkoutPayload(array $guests): array
    {
        return [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '09171234567',
            'payment_method' => 'paymongo',
            'terms_and_policy_accepted' => 'on',
            'camp_waiver_accepted' => 'on',
            'guests' => $guests,
            'cancellation_policy' => 'flexible',
        ];
    }

    private function sixGuestBooking(): array
    {
        return [
            'booking_type' => 'staycation',
            'checkin' => '2026-07-01',
            'checkout' => '2026-07-02',
            'adults_total' => 1,
            'children_total' => 0,
            'accommodations' => [
                [
                    'room_name' => 'Standard Glamping',
                    'adults' => 4,
                    'children' => 2,
                    'total_price' => 0,
                ],
            ],
            'selected_foods' => [],
            'selected_activities' => [],
            'selected_activity_packages' => [],
            'final_totals' => ['grand_total' => 0],
        ];
    }

    private function signatureData(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
    }
}
