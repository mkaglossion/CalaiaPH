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
            $table->string('image')->nullable()->after('price');
            $table->integer('max_guests')->nullable()->after('image');
            $table->string('pricing_type')->nullable()->after('max_guests');
            $table->boolean('is_active')->default(true)->after('pricing_type');
            $table->integer('max_groups_per_day')->nullable()->after('is_active');
            $table->json('price_configs')->nullable()->after('max_groups_per_day');
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
