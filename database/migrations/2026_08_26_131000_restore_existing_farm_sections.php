<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('farm_page_sections')) {
            return;
        }

        DB::table('farm_page_sections')
            ->whereIn('key', ['ethical_livestock', 'product_cards', 'field_banners'])
            ->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('farm_page_sections')) {
            return;
        }

        DB::table('farm_page_sections')
            ->whereIn('key', ['ethical_livestock', 'product_cards', 'field_banners'])
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }
};
