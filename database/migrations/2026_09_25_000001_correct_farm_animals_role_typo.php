<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('farm_experiences') || ! Schema::hasColumn('farm_experiences', 'desc')) {
            return;
        }

        DB::table('farm_experiences')
            ->where('desc', 'like', '%active roll%')
            ->update([
                'desc' => DB::raw("REPLACE(`desc`, 'active roll', 'active role')"),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('farm_experiences') || ! Schema::hasColumn('farm_experiences', 'desc')) {
            return;
        }

        DB::table('farm_experiences')
            ->where('desc', 'like', '%active role%')
            ->update([
                'desc' => DB::raw("REPLACE(`desc`, 'active role', 'active roll')"),
                'updated_at' => now(),
            ]);
    }
};