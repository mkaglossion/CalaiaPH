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
        if (!Schema::hasColumn('staycations', 'included_guest_capacity')) {
            Schema::table('staycations', function (Blueprint $table) {
                $table->unsignedInteger('included_guest_capacity')->nullable()->after('max_guest_capacity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('staycations', 'included_guest_capacity')) {
            Schema::table('staycations', function (Blueprint $table) {
                $table->dropColumn('included_guest_capacity');
            });
        }
    }
};
