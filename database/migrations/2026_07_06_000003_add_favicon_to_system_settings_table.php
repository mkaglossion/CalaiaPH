<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('system_settings', 'favicon_url')) {
                $table->text('favicon_url')->nullable()->after('logo_public_id');
            }

            if (!Schema::hasColumn('system_settings', 'favicon_public_id')) {
                $table->string('favicon_public_id')->nullable()->after('favicon_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (Schema::hasColumn('system_settings', 'favicon_public_id')) {
                $table->dropColumn('favicon_public_id');
            }

            if (Schema::hasColumn('system_settings', 'favicon_url')) {
                $table->dropColumn('favicon_url');
            }
        });
    }
};
