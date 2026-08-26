<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('system_settings', 'header_logo_width')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            $table->unsignedSmallInteger('header_logo_width')
                ->default(220)
                ->after('logo_public_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('system_settings', 'header_logo_width')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            $table->dropColumn('header_logo_width');
        });
    }
};
