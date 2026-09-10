<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'checkin_reminder_sent_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->timestamp('checkin_reminder_sent_at')->nullable()->after('checkout_timestamp')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'checkin_reminder_sent_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('checkin_reminder_sent_at');
            });
        }
    }
};
