<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderMobileStoreCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_cart_is_removed_from_mobile_top_bar_and_linked_inside_drawer(): void
    {
        $this->withSession([
            'cart' => [
                ['quantity' => 2],
                ['quantity' => 1],
            ],
        ]);

        $header = view('components.header', [
            'systemSettings' => SystemSetting::cachedData(),
        ])->render();

        $mobileActionsStart = strpos($header, 'data-mobile-header-actions');
        $drawerCartStart = strpos($header, 'data-mobile-store-cart-link');

        $this->assertNotFalse($mobileActionsStart);
        $this->assertNotFalse($drawerCartStart);
        $this->assertGreaterThan($mobileActionsStart, $drawerCartStart);

        $mobileTopBar = substr($header, $mobileActionsStart, $drawerCartStart - $mobileActionsStart);

        $this->assertStringNotContainsString('bi-cart3', $mobileTopBar);
        $this->assertStringContainsString('href="'.route('cart.index').'"', $header);
        $this->assertStringContainsString('Shopping Cart', $header);
        $this->assertStringContainsString('storeCartCount: 3', $header);
        $this->assertSame(1, substr_count($header, 'x-data="cartDropdown'));
    }
}
