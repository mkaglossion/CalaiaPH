<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('system_settings', 'mobile_header_logo_width')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            $table->unsignedSmallInteger('mobile_header_logo_width')
                ->default(220)
                ->after('header_logo_width');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('system_settings', 'mobile_header_logo_width')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            $table->dropColumn('mobile_header_logo_width');
        });
    }
};
