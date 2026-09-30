<?php

namespace Tests\Feature;

use App\Models\AddOnTransaction;
use App\Services\AddOnPaymentService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class NetworkResilienceTest extends TestCase
{
    public function test_paymongo_has_bounded_connect_and_total_timeouts(): void
    {
        $this->assertSame(3.0, (float) config('services.paymongo.connect_timeout'));
        $this->assertSame(10.0, (float) config('services.paymongo.timeout'));
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_add_on_checkout_recovery_falls_back_when_paymongo_times_out(): void
    {
        Http::fake(function () {
            throw new ConnectionException('PayMongo timed out.');
        });

        $addOn = new AddOnTransaction([
            'id' => 10,
            'paymongo_checkout_id' => 'cs_timeout',
        ]);

        $this->assertNull(app(AddOnPaymentService::class)->recoverCheckoutUrl($addOn));
    }
}
