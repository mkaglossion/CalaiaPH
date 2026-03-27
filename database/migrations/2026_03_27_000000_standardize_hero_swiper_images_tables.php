<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Standardize all hero swiper tables to have consistent structure:
     * - id (bigint unsigned, primary key, auto increment)
     * - title (varchar(255), nullable)
     * - image_path (text, not null)
     * - public_id (varchar(255), not null)
     * - created_at & updated_at (timestamps)
     */
    public function up(): void
    {
        $swiperTables = [
            'home_swiper_images',
            'day_tour_swiper_images',
            'stay_swiper_images',
            'do_swiper_images',
            'special_swiper_images',
            'farm_swiper_images',
            'package_swiper_images',
        ];

        foreach ($swiperTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    // Change public_id from text to varchar(255)
                    if (Schema::hasColumn($table->getTable(), 'public_id')) {
                        $table->string('public_id', 255)->change();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $swiperTables = [
            'home_swiper_images',
            'day_tour_swiper_images',
            'stay_swiper_images',
            'do_swiper_images',
            'special_swiper_images',
            'farm_swiper_images',
            'package_swiper_images',
        ];

        foreach ($swiperTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    // Revert public_id back to text
                    if (Schema::hasColumn($table->getTable(), 'public_id')) {
                        $table->text('public_id')->change();
                    }
                });
            }
        }
    }
};
