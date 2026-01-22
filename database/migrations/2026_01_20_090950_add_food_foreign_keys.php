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
        // Add foreign key constraint to food_swiper_images
        Schema::table('food_swiper_images', function (Blueprint $table) {
            $table->foreign('food_id')->references('id')->on('foods')->onDelete('cascade');
        });

        // Add foreign key constraint to food_texts
        Schema::table('food_texts', function (Blueprint $table) {
            $table->foreign('food_id')->references('id')->on('foods')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_swiper_images', function (Blueprint $table) {
            $table->dropForeign('food_swiper_images_food_id_foreign');
        });

        Schema::table('food_texts', function (Blueprint $table) {
            $table->dropForeign('food_texts_food_id_foreign');
        });
    }
};
