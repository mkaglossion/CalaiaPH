<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix package_swiper_images to match standard structure:
     * - image_path should be text, not null
     * - public_id should not be nullable
     */
    public function up(): void
    {
        if (Schema::hasTable('package_swiper_images')) {
            Schema::table('package_swiper_images', function (Blueprint $table) {
                // Change image_path to text and not nullable
                $table->text('image_path')->change();
                
                // Change public_id to not nullable
                $table->string('public_id')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('package_swiper_images')) {
            Schema::table('package_swiper_images', function (Blueprint $table) {
                $table->string('image_path')->nullable()->change();
                $table->string('public_id')->nullable()->change();
            });
        }
    }
};
