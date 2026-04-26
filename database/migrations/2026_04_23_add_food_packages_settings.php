<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create food package settings table
        Schema::create('food_package_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->text('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('food_package_settings')->insert([
            [
                'key' => 'title',
                'label' => 'Packages Section Title',
                'value' => 'Meal Packages (Special Bundles)',
                'description' => 'Title displayed for the food packages section in booking',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'description',
                'label' => 'Packages Section Description',
                'value' => 'Curated meal bundles with special pricing',
                'description' => 'Description for the food packages section',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'show',
                'label' => 'Show Packages Section',
                'value' => '1',
                'description' => 'Show or hide the packages section on booking page (1=show, 0=hide)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'icon',
                'label' => 'Packages Section Icon',
                'value' => 'bi-bag-heart',
                'description' => 'Bootstrap icon class for packages section',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_package_settings');
    }
};

