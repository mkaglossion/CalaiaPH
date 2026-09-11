<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_confirmation_contents', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_confirmation_contents', 'camp_waiver_form_url')) {
                $table->string('camp_waiver_form_url', 2048)->nullable()->after('camp_waiver_body');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_confirmation_contents', function (Blueprint $table) {
            if (Schema::hasColumn('booking_confirmation_contents', 'camp_waiver_form_url')) {
                $table->dropColumn('camp_waiver_form_url');
            }
        });
    }
};
