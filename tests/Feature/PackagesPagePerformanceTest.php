<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PackagesPagePerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_packages_page_does_not_embed_historical_inventory_in_package_cards(): void
    {
        $package = Package::create([
            'name' => 'Performance Test Package',
            'description' => 'Package page payload test.',
            'price' => 1000,
            'max_guests' => 4,
            'pricing_type' => 'per_group',
            'is_active' => true,
        ]);
        $now = now();
        $inventoryRows = [];

        for ($daysAgo = 429; $daysAgo >= 1; $daysAgo--) {
            $inventoryRows[] = [
                'package_id' => $package->id,
                'date' => $now->copy()->subDays($daysAgo)->toDateString(),
                'max_groups' => 3,
                'booked_groups' => 0,
                'available_groups' => 3,
                'is_blocked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $availableDate = $now->copy()->addDay();
        $inventoryRows[] = [
            'package_id' => $package->id,
            'date' => $availableDate->toDateString(),
            'max_groups' => 3,
            'booked_groups' => 1,
            'available_groups' => 2,
            'is_blocked' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        DB::table('package_inventory')->insert($inventoryRows);

        $response = $this->get('/packages')->assertOk();
        $html = $response->getContent();

        $response->assertSee('Next available date: '.$availableDate->format('M j, Y'))
            ->assertDontSee('No inventory available for booking yet.')
            ->assertDontSee('"package_id"', false);
        $this->assertLessThan(350_000, strlen($html));
    }
}
