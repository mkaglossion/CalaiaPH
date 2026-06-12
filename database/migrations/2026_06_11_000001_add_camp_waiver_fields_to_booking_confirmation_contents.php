<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_confirmation_contents', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_confirmation_contents', 'camp_waiver_title')) {
                $table->string('camp_waiver_title')->nullable()->after('payment_button_label');
            }

            if (!Schema::hasColumn('booking_confirmation_contents', 'camp_waiver_body')) {
                $table->text('camp_waiver_body')->nullable()->after('camp_waiver_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_confirmation_contents', function (Blueprint $table) {
            if (Schema::hasColumn('booking_confirmation_contents', 'camp_waiver_body')) {
                $table->dropColumn('camp_waiver_body');
            }

            if (Schema::hasColumn('booking_confirmation_contents', 'camp_waiver_title')) {
                $table->dropColumn('camp_waiver_title');
            }
        });
    }
};
