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
            if (!Schema::hasColumn('day_tours', 'image')) {
                $table->string('image')->nullable()->after('price');
            }
            if (!Schema::hasColumn('day_tours', 'max_guests')) {
                $table->integer('max_guests')->nullable()->after('image');
            }
            if (!Schema::hasColumn('day_tours', 'pricing_type')) {
                $table->string('pricing_type')->nullable()->after('max_guests');
            }
            if (!Schema::hasColumn('day_tours', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('pricing_type');
            }
            if (!Schema::hasColumn('day_tours', 'max_groups_per_day')) {
                $table->integer('max_groups_per_day')->nullable()->after('is_active');
            }
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
            $table->dropColumn(['image', 'max_guests', 'pricing_type', 'is_active', 'max_groups_per_day', 'price_configs']);
        });
    }
};
