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
        if (Schema::hasTable('package_swiper_images') && ! Schema::hasColumn('package_swiper_images', 'sort_order')) {
            Schema::table('package_swiper_images', function (Blueprint $table): void {
                $table->unsignedInteger('sort_order')->default(0)->after('public_id');
                $table->index('sort_order');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('package_swiper_images') && Schema::hasColumn('package_swiper_images', 'sort_order')) {
            Schema::table('package_swiper_images', function (Blueprint $table): void {
                $table->dropIndex(['sort_order']);
                $table->dropColumn('sort_order');
            });
        }
    }
};
