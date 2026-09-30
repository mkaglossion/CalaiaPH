<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmation;
use App\Models\Staycation;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaycationPriceBreakdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_paymongo_checkout_receives_the_staycation_breakdown(): void
    {
        config()->set('services.paymongo.secret_key', 'sk_test_123');
        Storage::fake('local');

        $room = Staycation::create([
            'name' => 'Premium Glamping',
            'days' => 1,
            'type' => 'glamping',
            'base_quantity' => 3,
            'capacity' => 4,
            'max_guest_capacity' => 6,
            'included_guest_capacity' => 4,
            'adults' => 4,
            'children' => 2,
            'price' => 0,
            'price_configs' => [[
                'type' => 'range',
                'startDate' => '2026-11-01',
                'endDate' => '2026-11-30',
                'days' => ['Sun', 'Mon'],
                'rates' => [
                    'nonPeak' => 9200,
                    'peak' => 9200,
                    'superPeak' => 9200,
                ],
            ]],
        ]);

        $capturedArguments = null;
        $this->partialMock(PaymentService::class, function ($mock) use (&$capturedArguments): void {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->withArgs(function (...$arguments) use (&$capturedArguments): bool {
                    $capturedArguments = $arguments;

                    return true;
                })
                ->andReturn((object) [
                    'checkout_url' => 'https://paymongo.test/checkout/cs_breakdown',
                    'id' => 'cs_breakdown',
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
                'skip_guest_details' => '1',
                'terms_and_policy_accepted' => 'on',
                'camp_waiver_accepted' => 'on',
            ],
            [
                'booking' => [
                    'booking_type' => 'staycation',
                    'checkin' => '2026-11-15',
                    'checkout' => '2026-11-17',
                    'accommodations' => [[
                        'room_id' => $room->id,
                        'accommodation_id' => $room->id,
                        'adults' => 4,
                        'children' => 1,
                        'pets' => 'no',
                        'num_pets' => 0,
                        'extra_mattress' => 0,
                    ]],
                    'selected_foods' => [],
                    'selected_activities' => [],
                    'selected_activity_packages' => [],
                    'final_totals' => ['grand_total' => 0],
                ],
            ]
        );

        $response->assertRedirect('https://paymongo.test/checkout/cs_breakdown');
        $this->assertNotNull($capturedArguments);
        if (!is_array($capturedArguments)) {
            $this->fail('PayMongo checkout arguments were not captured.');
        }

        $this->assertSame(2090000, $capturedArguments[0]);

        $lineItems = $capturedArguments[4];
        $this->assertSame([
            'Premium Glamping - Nov 15, 2026',
            'Premium Glamping - Nov 16, 2026',
            'Extra guest/mattress (1 guest x 2 nights)',
        ], array_column($lineItems, 'name'));
        $this->assertSame([9200.0, 9200.0, 1250.0], array_column($lineItems, 'unit_price'));
        $this->assertSame([1, 1, 2], array_column($lineItems, 'quantity'));
    }

    public function test_confirmation_page_and_email_render_the_same_breakdown(): void
    {
        $room = Staycation::create([
            'name' => 'Premium Glamping',
            'days' => 1,
            'type' => 'glamping',
            'base_quantity' => 3,
            'capacity' => 4,
            'max_guest_capacity' => 6,
            'included_guest_capacity' => 4,
            'adults' => 4,
            'children' => 2,
            'price' => 0,
        ]);

        $bookingData = [
            'booking_type' => 'staycation',
            'transaction_id' => 'TXN-BREAKDOWN-1',
            'checkin' => '2026-11-15',
            'checkout' => '2026-11-17',
            'total_guests' => 5,
            'accommodations' => [[
                'room_id' => $room->id,
                'accommodation_id' => $room->id,
                'room_name' => 'Premium Glamping',
                'adults' => 4,
                'children' => 1,
                'accommodation_price' => 18400,
                'base_price' => 18400,
                'additional_adults' => 1,
                'additional_adult_price' => 2500,
                'pet_price' => 0,
                'total_price' => 20900,
            ]],
            'selected_foods' => [],
            'selected_activities' => [],
            'selected_activity_packages' => [],
            'final_totals' => [
                'accommodation_total' => 20900,
                'food_total' => 0,
                'activities_total' => 0,
                'grand_total' => 20900,
            ],
        ];

        $mail = new BookingConfirmation([
            'bookingData' => $bookingData,
            'customerInfo' => ['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'email' => 'juan@example.com'],
            'staycations' => new Collection([$room->id => $room]),
            'selectedFoods' => [],
            'selectedActivities' => [],
            'selectedActivityPackages' => [],
            'isStaycation' => true,
        ]);

        $emailHtml = $mail->render();
        $this->assertStringContainsString('Premium Glamping - Nov 15, 2026: &#8369;9,200.00', $emailHtml);
        $this->assertStringContainsString('Extra guest/mattress (1 guest x 2 nights): &#8369;1,250.00 &times; 2 = &#8369;2,500.00', $emailHtml);
        $this->assertStringContainsString('Total Amount Paid:</strong> &#8369;20,900.00', $emailHtml);

        $response = $this->withSession([
            'booking' => $bookingData,
            'staycation_customer_info' => ['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'email' => 'juan@example.com'],
        ])->get(route('booking.confirmation'));

        $response->assertOk()
            ->assertSee('Premium Glamping - Nov 15, 2026')
            ->assertSee('Extra guest/mattress (1 guest x 2 nights)')
            ->assertSee('Nights:')
            ->assertSee('>2</span>', false)
            ->assertSee('&#8369;20,900.00', false);
    }
}
