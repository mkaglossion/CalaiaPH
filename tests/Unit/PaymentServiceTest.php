<?php

namespace Tests\Unit;

use App\Services\PaymentService;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    public function test_create_checkout_preserves_discounted_final_amount_when_line_items_do_not_match(): void
    {
        config()->set('services.paymongo.secret_key', 'sk_test_123');

        $capturedPayload = null;

        $client = Mockery::mock('overload:GuzzleHttp\Client');
        $client->shouldReceive('post')
            ->once()
            ->with(
                'https://api.paymongo.com/v1/checkout_sessions',
                Mockery::on(function (array $options) use (&$capturedPayload) {
                    $capturedPayload = $options['json'] ?? null;

                    return isset($options['json']['data']['attributes']);
                })
            )
            ->andReturn(new Response(200, [], json_encode([
                'data' => [
                    'id' => 'cs_test_123',
                    'attributes' => [
                        'checkout_url' => 'https://paymongo.test/checkout/cs_test_123',
                        'payment_method_types' => ['card', 'gcash'],
                    ],
                ],
            ])));

        $service = new PaymentService();

        $checkout = $service->createCheckout(
            1376000,
            'staycation',
            'https://example.com/success',
            'https://example.com/cancel',
            [
                [
                    'name' => 'Staycation Booking',
                    'unit_price' => 14260.00,
                    'quantity' => 1,
                ],
            ],
            'TXN-SAVE500',
            'guest@example.com'
        );

        $this->assertSame('cs_test_123', $checkout->id);
        $this->assertSame('https://paymongo.test/checkout/cs_test_123', $checkout->checkout_url);
        $this->assertSame(1376000, data_get($capturedPayload, 'data.attributes.amount'));

        $lineItems = data_get($capturedPayload, 'data.attributes.line_items');

        $this->assertIsArray($lineItems);
        $this->assertCount(1, $lineItems);
        $this->assertSame(1376000, $lineItems[0]['amount']);
        $this->assertSame(1, $lineItems[0]['quantity']);
    }
}
