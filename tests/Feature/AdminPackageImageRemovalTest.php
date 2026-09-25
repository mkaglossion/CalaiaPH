<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPackageImageRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_remove_package_image_without_uploading_a_replacement(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $package = Package::create([
            'name' => 'ATV Adventure',
            'slug' => 'atv-adventure',
            'description' => 'Trail ride package.',
            'price' => 1200,
            'pricing_type' => 'per_pax',
            'image' => 'https://res.cloudinary.com/demo/image/upload/sample.jpg',
            'image_public_id' => null,
            'duration_days' => 1,
            'duration_value' => 3,
            'duration_unit' => 'hours',
            'max_guests' => 8,
            'max_groups_per_day' => 3,
            'inclusions' => ['Helmet'],
            'sessions' => [['name' => 'Morning', 'start' => '08:00', 'end' => '11:00']],
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->putJson(route('admin.packages.update', $package), [
            'name' => 'ATV Adventure',
            'description' => 'Trail ride package.',
            'price' => 1200,
            'pricing_type' => 'per_pax',
            'duration_value' => 3,
            'duration_unit' => 'hours',
            'max_guests' => 8,
            'max_groups_per_day' => 3,
            'inclusions' => json_encode(['Helmet']),
            'sessions' => json_encode([['name' => 'Morning', 'start' => '08:00', 'end' => '11:00']]),
            'is_active' => true,
            'remove_image' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Package updated successfully.',
            ]);

        $package->refresh();

        $this->assertNull($package->image);
        $this->assertNull($package->image_public_id);
    }
}
