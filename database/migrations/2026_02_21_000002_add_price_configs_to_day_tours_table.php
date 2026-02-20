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
        Schema::table('day_tours', function (Blueprint $table) {
            if (!Schema::hasColumn('day_tours', 'price_configs')) {
                $table->json('price_configs')->nullable()->after('max_groups_per_day');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_tours', function (Blueprint $table) {
            if (Schema::hasColumn('day_tours', 'price_configs')) {
                $table->dropColumn('price_configs');
            }
        });
    }
};
