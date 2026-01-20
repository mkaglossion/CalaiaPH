<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add food_id to food_swiper_images
        Schema::table('food_swiper_images', function (Blueprint $table) {
            if (!Schema::hasColumn('food_swiper_images', 'food_id')) {
                $table->unsignedBigInteger('food_id')->nullable()->after('id');
            }
        });

        // Add food_id to food_texts
        Schema::table('food_texts', function (Blueprint $table) {
            if (!Schema::hasColumn('food_texts', 'food_id')) {
                $table->unsignedBigInteger('food_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_swiper_images', function (Blueprint $table) {
            if (Schema::hasColumn('food_swiper_images', 'food_id')) {
                $table->dropColumn('food_id');
            }
        });

        Schema::table('food_texts', function (Blueprint $table) {
            if (Schema::hasColumn('food_texts', 'food_id')) {
                $table->dropColumn('food_id');
            }
        });
    }
};
