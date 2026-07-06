<?php

namespace Tests\Feature;

use App\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PromoCodeValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_endpoint_accepts_promo_for_entire_end_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-09 14:30:00', config('app.timezone')));

        PromoCode::create([
            'code' => 'WELCOME10',
            'name' => 'Welcome Discount',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'start_date' => '2026-05-09',
            'end_date' => '2026-06-09',
            'usage_limit' => null,
            'usage_count' => 0,
            'min_booking_amount' => null,
            'is_active' => true,
        ]);

        $response = $this->postJsonWithCsrf(route('api.promo-codes.apply'), [
            'code' => 'WELCOME10',
            'booking_total' => 5000,
        ]);

        $response->assertOk()
            ->assertJson([
                'valid' => true,
                'promo_code' => [
                    'code' => 'WELCOME10',
                    'discount_amount' => 500,
                ],
                'new_total' => 4500,
            ]);

        Carbon::setTestNow();
    }
}
