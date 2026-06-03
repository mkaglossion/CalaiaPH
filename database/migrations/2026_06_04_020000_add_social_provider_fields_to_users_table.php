<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider_name', 32)->nullable()->after('email_verified_at');
            $table->string('provider_id')->nullable()->after('provider_name');
            $table->text('avatar')->nullable()->after('provider_id');
            $table->unique(['provider_name', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['provider_name', 'provider_id']);
            $table->dropColumn(['provider_name', 'provider_id', 'avatar']);
        });
    }
};
