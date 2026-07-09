<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_package_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->text('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('activity_package_settings')->insert([
            [
                'key' => 'adventure_label',
                'label' => 'Adventure Label',
                'value' => 'Adventure',
                'description' => 'Shown in activity filters, cards, and admin category choices.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sports_label',
                'label' => 'Sports & Games Label',
                'value' => 'Sports & Games',
                'description' => 'Shown in activity filters, cards, and admin category choices.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'rrcamp_label',
                'label' => 'R & R & Camp Label',
                'value' => 'R & R & Camp',
                'description' => 'Shown in activity filters, cards, and admin category choices.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'farm_label',
                'label' => 'Farm Label',
                'value' => 'Farm',
                'description' => 'Shown in activity filters, cards, and admin category choices.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'special_label',
                'label' => 'Special Label',
                'value' => 'Special',
                'description' => 'Shown in activity package cards and admin category choices.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'packages_section_heading',
                'label' => 'Activity Packages Section Heading',
                'value' => 'Activity Packages',
                'description' => 'Booking section heading.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'packages_section_description',
                'label' => 'Activity Packages Section Description',
                'value' => 'Curated activity bundles with special pricing',
                'description' => 'Booking section description.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'adventure_section_heading',
                'label' => 'Adventure Section Heading',
                'value' => 'Adventure',
                'description' => 'Booking section heading.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'adventure_section_description',
                'label' => 'Adventure Section Description',
                'value' => 'Choose from activities a la carte below.',
                'description' => 'Booking section description.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sports_section_heading',
                'label' => 'Sports & Games Section Heading',
                'value' => 'Sports & Games',
                'description' => 'Booking section heading.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sports_section_description',
                'label' => 'Sports & Games Section Description',
                'value' => 'Choose from activities a la carte below.',
                'description' => 'Booking section description.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'rrcamp_section_heading',
                'label' => 'R & R & Camp Section Heading',
                'value' => 'R & R & Camp',
                'description' => 'Booking section heading.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'rrcamp_section_description',
                'label' => 'R & R & Camp Section Description',
                'value' => 'Choose from activities a la carte below.',
                'description' => 'Booking section description.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'farm_section_heading',
                'label' => 'Farm Section Heading',
                'value' => 'Farm',
                'description' => 'Booking section heading.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'farm_section_description',
                'label' => 'Farm Section Description',
                'value' => 'Choose from activities a la carte below.',
                'description' => 'Booking section description.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'special_section_heading',
                'label' => 'Special Section Heading',
                'value' => 'Special',
                'description' => 'Booking section heading.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'special_section_description',
                'label' => 'Special Section Description',
                'value' => 'Choose from special activities below.',
                'description' => 'Booking section description.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_package_settings');
    }
};
