<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('staycations', 'sort_order')) {
            Schema::table('staycations', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('type');
                $table->index('sort_order');
            });
        }

        $staycationIds = DB::table('staycations')
            ->orderBy('id')
            ->pluck('id');

        foreach ($staycationIds as $index => $staycationId) {
            DB::table('staycations')
                ->where('id', $staycationId)
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('staycations', 'sort_order')) {
            return;
        }

        Schema::table('staycations', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
