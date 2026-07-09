<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('food_packages', 'sections')) {
            Schema::table('food_packages', function (Blueprint $table) {
                $table->json('sections')->nullable()->after('image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('food_packages', 'sections')) {
            Schema::table('food_packages', function (Blueprint $table) {
                $table->dropColumn('sections');
            });
        }
    }
};
