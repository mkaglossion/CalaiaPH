<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('packages') || Schema::hasColumn('packages', 'sort_order')) {
            return;
        }

        Schema::table('packages', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->after('max_groups_per_day');
            $table->index('sort_order');
        });

        $packages = DB::table('packages')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id']);

        foreach ($packages as $index => $package) {
            DB::table('packages')
                ->where('id', $package->id)
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('packages') || ! Schema::hasColumn('packages', 'sort_order')) {
            return;
        }

        Schema::table('packages', function (Blueprint $table): void {
            $table->dropIndex(['sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
