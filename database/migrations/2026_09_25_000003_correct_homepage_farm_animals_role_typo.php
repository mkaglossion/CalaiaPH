<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('event_experience_sections') || ! Schema::hasColumn('event_experience_sections', 'description2')) {
            return;
        }

        DB::table('event_experience_sections')
            ->where('description2', 'like', '%active roll%')
            ->update([
                'description2' => DB::raw("REPLACE(description2, 'active roll', 'active role')"),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('event_experience_sections') || ! Schema::hasColumn('event_experience_sections', 'description2')) {
            return;
        }

        DB::table('event_experience_sections')
            ->where('description2', 'like', '%active role%')
            ->update([
                'description2' => DB::raw("REPLACE(description2, 'active role', 'active roll')"),
                'updated_at' => now(),
            ]);
    }
};