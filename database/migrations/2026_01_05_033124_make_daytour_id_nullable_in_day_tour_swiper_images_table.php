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
        Schema::table('day_tour_swiper_images', function (Blueprint $table) {
            if (!Schema::hasColumn('day_tour_swiper_images', 'daytour_id')) {
                $table->unsignedBigInteger('daytour_id')->nullable()->after('id');
            } else {
                $table->foreignId('daytour_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_tour_swiper_images', function (Blueprint $table) {
            $table->foreignId('daytour_id')->nullable(false)->change();
        });
    }
};
