<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class StoreCartLogoHeaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_cart_logo_is_displayed_on_the_brand_background(): void
    {
        $setting = SystemSetting::current();
        $setting->logo_url = 'https://res.cloudinary.com/demo/image/upload/sample.png';
        $setting->header_logo_width = 304;
        $setting->mobile_header_logo_width = 184;
        $setting->save();
        View::share('systemSettings', SystemSetting::refreshCache());

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('data-store-cart-logo-header', false)
            ->assertSee('class="bg-brand shadow-sm"', false)
            ->assertDontSee('border-brand-dark', false)
            ->assertSee('class="group inline-flex w-full items-center justify-center transition-transform duration-300 hover:scale-105"', false)
            ->assertSee('class="hidden md:block mx-auto drop-shadow-sm', false)
            ->assertSee('class="block md:hidden mx-auto drop-shadow-sm', false)
            ->assertSee('/upload/e_trim/f_auto', false)
            ->assertSee('data-logo-viewport="desktop"', false)
            ->assertSee('data-logo-width="304"', false)
            ->assertSee('width: 304px; max-width: 100%; height: auto;', false)
            ->assertSee('data-logo-viewport="mobile"', false)
            ->assertSee('data-logo-width="184"', false)
            ->assertSee('width: min(184px, 100%); height: auto;', false);
    }
}
