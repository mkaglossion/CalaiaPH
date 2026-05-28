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
        if (!Schema::hasTable('packages')) {
            return;
        }

        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'duration_value')) {
                $table->unsignedInteger('duration_value')->nullable()->after('duration_days');
            }

            if (!Schema::hasColumn('packages', 'duration_unit')) {
                $table->string('duration_unit', 16)->nullable()->after('duration_value');
            }
        });

        DB::table('packages')
            ->where('pricing_type', 'per_group')
            ->update(['pricing_type' => 'per_pax']);

        DB::table('packages')
            ->whereNull('duration_value')
            ->whereNotNull('duration_days')
            ->update([
                'duration_value' => DB::raw('duration_days'),
                'duration_unit' => 'days',
            ]);

        DB::table('packages')
            ->whereNull('duration_unit')
            ->update(['duration_unit' => 'days']);

        DB::table('packages')
            ->whereNull('duration_value')
            ->update(['duration_value' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('packages')) {
            return;
        }

        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'duration_unit')) {
                $table->dropColumn('duration_unit');
            }

            if (Schema::hasColumn('packages', 'duration_value')) {
                $table->dropColumn('duration_value');
            }
        });
    }
};
