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
        $tables = [
            'home_swiper_images',
            'stay_swiper_images',
            'day_tour_swiper_images',
            'do_swiper_images',
            'special_swiper_images',
            'farm_swiper_images',
            'shop_swiper_images',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'sort_order')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->unsignedInteger('sort_order')->default(0)->after('public_id');
                $blueprint->index('sort_order');
            });

            $ids = DB::table($table)
                ->orderBy('created_at')
                ->orderBy('id')
                ->pluck('id')
                ->all();

            foreach ($ids as $index => $id) {
                DB::table($table)
                    ->where('id', $id)
                    ->update(['sort_order' => $index + 1]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'home_swiper_images',
            'stay_swiper_images',
            'day_tour_swiper_images',
            'do_swiper_images',
            'special_swiper_images',
            'farm_swiper_images',
            'shop_swiper_images',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'sort_order')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropIndex(['sort_order']);
                $blueprint->dropColumn('sort_order');
            });
        }
    }
};
