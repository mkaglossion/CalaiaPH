<?php

namespace Tests\Feature;

use App\Models\FarmPageSection;
use App\Models\User;
use App\Services\CloudinaryImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;
use Tests\TestCase;

class FarmPageSectionsCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_farm_sections_are_seeded_and_rendered_on_the_public_page(): void
    {
        $this->assertDatabaseCount('farm_page_sections', 5);
        $this->assertNull(config('farm_page_sections.sections.ethical_livestock.image_url'));
        $this->assertNull(config('farm_page_sections.sections.ethical_livestock.secondary_image_url'));
        $this->assertTrue(config('farm_page_sections.sections.ethical_livestock.is_active'));
        $this->assertTrue(config('farm_page_sections.sections.product_cards.is_active'));
        $this->assertTrue(config('farm_page_sections.sections.field_banners.is_active'));
        $this->assertTrue(
            collect(config('farm_page_sections.sections'))
                ->flatMap(fn (array $section) => $section['items'] ?? [])
                ->every(fn (array $item) => blank($item['image_url'] ?? null))
        );

        $response = $this->get(route('farm'));

        $response->assertOk();
        $response->assertSee('Life on the Farm');
        $response->assertSee('Nurtured Ecosystems');
        $response->assertSee('Soil First');
        $response->assertSee('Ethical Livestock Management');
        $response->assertSee('Respect for Natural Cycles');
        $response->assertSee('Forest Regeneration');
        $response->assertSee('class="mb-3 flex min-h-14 w-full items-start justify-center text-xl font-bold leading-tight text-white"', false);
        $response->assertSee('Ethically raised livestock.');
        $response->assertSee('Grass-Fed Beef');
        $response->assertSee('Vegetable Garden.');
        $response->assertDontSee('assets/images/carousel/', false);
        $response->assertDontSee('assets/icons/', false);
        $response->assertDontSee('data:image/svg+xml', false);
        $response->assertSee('class="w-full px-4 py-16 sm:px-6 md:py-20 lg:px-8"', false);
        $response->assertSee('class="fas fa-tractor text-4xl md:text-5xl opacity-80 mb-4 block"', false);
    }

    public function test_admin_can_update_repeatable_section_content_and_visibility(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $section = FarmPageSection::query()->where('key', 'product_cards')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.farm.ourfarm'))
            ->assertOk()
            ->assertSee('data-page-copy-manager', false)
            ->assertSee('Page Sections')
            ->assertSee('Farm Product Cards')
            ->assertDontSee('Checklist Cards')
            ->assertSee('data-farm-section-item', false)
            ->assertSee('data-drag-handle', false)
            ->assertSee('role="switch"', false)
            ->assertSee('toggleSectionVisibility', false)
            ->assertSee('deleteSectionImage', false)
            ->assertSee('removeItemImage', false)
            ->assertSee('Sortable.create', false);

        $response = $this->actingAs($admin)->putJson(route('admin.farm.sections.update', $section), [
            'title' => null,
            'description' => null,
            'image_url' => null,
            'image_public_id' => null,
            'secondary_image_url' => null,
            'secondary_image_public_id' => null,
            'items' => [[
                'key' => 'seasonal-harvest',
                'title' => 'Seasonal Harvest Box',
                'description' => 'Fresh produce selected from the farm.',
                'meta' => 'Available every weekend',
                'alignment' => 'left',
                'image_url' => 'https://res.cloudinary.com/demo/image/upload/farm-box.jpg',
                'image_public_id' => 'calaia/farm/page-sections/farm-box',
            ]],
            'settings' => [],
            'is_active' => true,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('farm_page_sections', ['key' => 'product_cards']);

        $this->get(route('farm'))
            ->assertOk()
            ->assertSee('Seasonal Harvest Box')
            ->assertSee('farm-box.jpg', false)
            ->assertDontSee('Grass-Fed Beef');

        $payload = $section->fresh()->only([
            'title', 'description', 'image_url', 'image_public_id',
            'secondary_image_url', 'secondary_image_public_id', 'items', 'settings',
        ]);
        $payload['is_active'] = false;

        $this->actingAs($admin)
            ->putJson(route('admin.farm.sections.update', $section), $payload)
            ->assertOk();

        $this->get(route('farm'))->assertDontSee('Seasonal Harvest Box');
    }

    public function test_section_image_upload_uses_cloudinary_service(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->mock(CloudinaryImageService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('upload')
                ->once()
                ->withArgs(fn (UploadedFile $file, string $folder) => $file->getClientOriginalName() === 'farm.jpg' && $folder === 'farm/page-sections')
                ->andReturn([
                    'url' => 'https://res.cloudinary.com/demo/image/upload/farm.jpg',
                    'public_id' => 'calaia/farm/page-sections/farm',
                    'width' => 1600,
                    'height' => 900,
                ]);
        });

        $response = $this->actingAs($admin)->post(route('admin.farm.sections.upload'), [
            'image' => UploadedFile::fake()->image('farm.jpg', 1600, 900)->size(500),
        ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJson([
            'success' => true,
            'image' => [
                'url' => 'https://res.cloudinary.com/demo/image/upload/farm.jpg',
                'public_id' => 'calaia/farm/page-sections/farm',
            ],
        ]);
    }

    public function test_section_card_order_is_persisted(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $section = FarmPageSection::query()->where('key', 'product_cards')->firstOrFail();
        $reorderedItems = array_reverse($section->items);

        $payload = $section->only([
            'title', 'description', 'image_url', 'image_public_id',
            'secondary_image_url', 'secondary_image_public_id', 'settings',
        ]);
        $payload['items'] = $reorderedItems;
        $payload['is_active'] = true;

        $this->actingAs($admin)
            ->putJson(route('admin.farm.sections.update', $section), $payload)
            ->assertOk();

        $this->assertSame(
            collect($reorderedItems)->pluck('key')->all(),
            collect($section->fresh()->items)->pluck('key')->all()
        );
    }

    public function test_admin_can_delete_a_cloudinary_section_image(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $section = FarmPageSection::query()->where('key', 'ethical_livestock')->firstOrFail();
        $section->update([
            'image_url' => 'https://res.cloudinary.com/demo/image/upload/farm-banner.jpg',
            'image_public_id' => 'calaia/farm/page-sections/farm-banner',
        ]);

        $this->mock(CloudinaryImageService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('destroy')
                ->once()
                ->with('calaia/farm/page-sections/farm-banner');
        });

        $payload = $section->only([
            'title', 'description', 'secondary_image_url', 'secondary_image_public_id',
            'items', 'settings', 'is_active',
        ]);
        $payload['image_url'] = null;
        $payload['image_public_id'] = null;

        $this->actingAs($admin)
            ->putJson(route('admin.farm.sections.update', $section), $payload)
            ->assertOk();

        $this->assertNull($section->fresh()->image_url);
        $this->assertNull($section->fresh()->image_public_id);
    }

    public function test_farm_cta_saved_in_cms_renders_through_the_shared_component(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        $this->actingAs($admin)->postJson(route('admin.farm.copy.store'), [
            'page_title' => 'Our Sustainable Farming',
            'page_intro' => 'Farm page introduction.',
            'cta_title' => 'Plan Your Farm Visit',
            'cta_intro' => 'Ask our team about schedules and group arrangements.',
            'cta_button' => 'Chat With Calaia',
            'cta_note' => 'Replies are sent during business hours.',
        ])->assertOk();

        $this->get(route('farm'))
            ->assertOk()
            ->assertSee('Plan Your Farm Visit')
            ->assertSee('Ask our team about schedules and group arrangements.')
            ->assertSee('Chat With Calaia')
            ->assertSee('Replies are sent during business hours.')
            ->assertSee('href="https://m.me/410774518796277"', false);
    }

    public function test_non_admin_cannot_manage_farm_sections(): void
    {
        $customer = User::factory()->create(['user_type' => 'client']);
        $section = FarmPageSection::query()->firstOrFail();

        $this->actingAs($customer)
            ->putJson(route('admin.farm.sections.update', $section), [
                'items' => [],
                'settings' => [],
                'is_active' => true,
            ])
            ->assertForbidden();
    }
}
