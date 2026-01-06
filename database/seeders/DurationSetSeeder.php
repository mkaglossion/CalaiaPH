<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DurationSet;

class DurationSetSeeder extends Seeder
{
    public function run(): void
    {
        // Create a default duration set for nightly pricing
        DurationSet::updateOrCreate(
            ['name' => 'Nightly Rate'],
            ['duration' => 1] // 1 night
        );
    }
}