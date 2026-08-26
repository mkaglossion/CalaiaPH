<?php

use App\Models\InformationPage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('information_pages', function (Blueprint $table) {
            $table->string('contact_phone', 30)->nullable()->after('hero_description');
        });

        DB::table('information_pages')
            ->where('slug', 'contact')
            ->whereNull('contact_phone')
            ->update(['contact_phone' => InformationPage::DEFAULT_CONTACT_PHONE]);
    }

    public function down(): void
    {
        Schema::table('information_pages', function (Blueprint $table) {
            $table->dropColumn('contact_phone');
        });
    }
};
