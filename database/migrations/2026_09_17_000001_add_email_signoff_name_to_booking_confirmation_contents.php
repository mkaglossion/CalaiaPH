<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_confirmation_contents', function (Blueprint $table) {
            $table->string('email_signoff_name')->nullable()->after('email_closing');
        });
    }

    public function down(): void
    {
        Schema::table('booking_confirmation_contents', function (Blueprint $table) {
            $table->dropColumn('email_signoff_name');
        });
    }
};
