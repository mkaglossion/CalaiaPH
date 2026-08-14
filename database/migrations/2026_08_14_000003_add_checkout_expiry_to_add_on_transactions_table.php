<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('add_on_transactions', function (Blueprint $table) {
            $table->text('checkout_url')->nullable()->after('paymongo_checkout_id');
            $table->timestamp('expires_at')->nullable()->after('checkout_url')->index();
            $table->timestamp('expired_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('add_on_transactions', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
            $table->dropColumn(['checkout_url', 'expires_at', 'expired_at']);
        });
    }
};
