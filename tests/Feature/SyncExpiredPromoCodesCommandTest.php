<?php

namespace Tests\Feature;

use App\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SyncExpiredPromoCodesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_marks_expired_active_promo_codes_as_inactive(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-06 10:00:00', config('app.timezone')));

        $expiredPromo = PromoCode::create([
            'code' => 'FLASH50',
            'name' => 'Flash Sale 50%',
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'start_date' => '2026-05-09',
            'end_date' => '2026-07-05',
            'usage_limit' => null,
            'usage_count' => 0,
            'min_booking_amount' => null,
            'is_active' => true,
        ]);

        $activePromo = PromoCode::create([
            'code' => 'WELCOME10',
            'name' => 'Welcome Discount',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'start_date' => '2026-06-01',
            'end_date' => '2026-07-10',
            'usage_limit' => null,
            'usage_count' => 0,
            'min_booking_amount' => null,
            'is_active' => true,
        ]);

        $usedUpPromo = PromoCode::create([
            'code' => 'MAX20',
            'name' => 'Max Uses Reached',
            'discount_type' => 'fixed',
            'discount_value' => 100,
            'start_date' => '2026-06-01',
            'end_date' => '2026-07-10',
            'usage_limit' => 20,
            'usage_count' => 20,
            'min_booking_amount' => null,
            'is_active' => true,
        ]);

        $this->artisan('promo-codes:sync-expired')
            ->expectsOutput('Expired or exhausted 2 promo code(s) marked inactive.')
            ->assertSuccessful();

        $expiredPromo->refresh();
        $activePromo->refresh();
        $usedUpPromo->refresh();

        $this->assertFalse($expiredPromo->is_active);
        $this->assertTrue($activePromo->is_active);
        $this->assertFalse($usedUpPromo->is_active);

        Carbon::setTestNow();
    }
}
