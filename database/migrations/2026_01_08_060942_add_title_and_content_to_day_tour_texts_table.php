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
        Schema::table('day_tour_texts', function (Blueprint $table) {
            if (!Schema::hasColumn('day_tour_texts', 'title')) {
                $table->string('title')->nullable()->after('id');
            }
            if (!Schema::hasColumn('day_tour_texts', 'content')) {
                $table->longText('content')->nullable()->after('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_tour_texts', function (Blueprint $table) {
            if (Schema::hasColumn('day_tour_texts', 'content')) {
                $table->dropColumn('content');
            }
            if (Schema::hasColumn('day_tour_texts', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};
