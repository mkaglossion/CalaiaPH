<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add unit_type to pricing_configs for rate matrix
        if (!Schema::hasColumn('pricing_configs', 'unit_type')) {
            Schema::table('pricing_configs', function (Blueprint $table) {
                $table->string('unit_type')->default('')->after('id')->comment('e.g., Glamping Tent, Cabin, Villa');
            });
        }

        // Add peak_type_id to pricing_configs (for direct peak type reference instead of peak_period_id)
        if (!Schema::hasColumn('pricing_configs', 'peak_type_id')) {
            Schema::table('pricing_configs', function (Blueprint $table) {
                $table->foreignId('peak_type_id')->nullable()->constrained()->cascadeOnDelete()->after('unit_type');
            });
        }

        // Add name column to peak_periods for optional naming (e.g., "Holy Week", "Halloween")
        if (!Schema::hasColumn('peak_periods', 'name')) {
            Schema::table('peak_periods', function (Blueprint $table) {
                $table->string('name')->nullable()->after('peak_type_id')->comment('Optional name for the period');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pricing_configs', function (Blueprint $table) {
            $table->dropForeign(['peak_type_id']);
            $table->dropColumn(['peak_type_id', 'unit_type']);
        });

        Schema::table('peak_periods', function (Blueprint $table) {
            $table->dropColumn(['name']);
        });
    }
};
