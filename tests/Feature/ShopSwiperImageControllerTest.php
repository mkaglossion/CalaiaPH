<?php

namespace Tests\Feature;

use App\Models\ShopSwiperImage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShopSwiperImageControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('shop_swiper_images')) {
            Schema::create('shop_swiper_images', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->text('image_path');
                $table->string('public_id');
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('shop_swiper_images');

        parent::tearDown();
    }

    public function test_destroy_returns_success_when_image_already_deleted(): void
    {
        $image = ShopSwiperImage::create([
            'title' => 'Hero Slide',
            'image_path' => 'https://example.com/shop.jpg',
            'public_id' => '',
        ]);

        $response = $this->withoutMiddleware()
            ->deleteJson(route('admin.shop-swiper-images.destroy', $image->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Image deleted successfully',
            ]);

        $this->assertDatabaseMissing('shop_swiper_images', [
            'id' => $image->id,
        ]);

        $secondResponse = $this->withoutMiddleware()
            ->deleteJson(route('admin.shop-swiper-images.destroy', $image->id));

        $secondResponse->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Image already deleted.',
            ]);
    }
}
