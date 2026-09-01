<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('information_pages', function (Blueprint $table): void {
            $table->boolean('is_hero_enabled')->default(true)->after('hero_description');
        });

        Schema::table('policy_pages', function (Blueprint $table): void {
            $table->boolean('is_hero_enabled')->default(true)->after('hero_description');
        });
    }

    public function down(): void
    {
        Schema::table('information_pages', function (Blueprint $table): void {
            $table->dropColumn('is_hero_enabled');
        });

        Schema::table('policy_pages', function (Blueprint $table): void {
            $table->dropColumn('is_hero_enabled');
        });
    }
};
