<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Package;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (! Schema::hasColumn('packages', 'sessions')) {
                $table->json('sessions')->nullable()->after('inclusions');
            }
        });

        // Set default sessions for existing packages
        Package::whereNull('sessions')->update([
            'sessions' => json_encode([
                ['name' => 'Morning', 'start' => '8:00 AM', 'end' => '11:00 AM'],
                ['name' => 'Afternoon', 'start' => '12:00 PM', 'end' => '3:00 PM'],
                ['name' => 'Late Session', 'start' => '3:30 PM', 'end' => '6:30 PM']
            ])
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('sessions');
        });
    }
};
