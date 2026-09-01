<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivitiesPackageDescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_package_tiles_show_the_full_description_without_line_clamping(): void
    {
        $description = 'This complete package description includes every important detail and must remain visible through the final sentence on the public tile.';

        Package::create([
            'name' => 'Complete Description Package',
            'description' => $description,
            'price' => 950,
            'max_guests' => 10,
            'pricing_type' => 'per_pax',
            'is_active' => true,
            'duration_value' => 5,
            'duration_unit' => 'hours',
            'sort_order' => 1,
        ]);

        $this->get(route('activities'))
            ->assertOk()
            ->assertSee($description)
            ->assertSee('class="text-gray-600 text-xs sm:text-sm mb-2 grow min-h-6"', false)
            ->assertDontSee('line-clamp-2 sm:line-clamp-3', false);
    }
}
