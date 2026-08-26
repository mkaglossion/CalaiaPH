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
            $table->string('contact_address', 500)->nullable()->after('hero_description');
            $table->string('contact_email')->nullable()->after('contact_phone');
            $table->string('contact_hours', 255)->nullable()->after('contact_email');
        });

        $contactPage = DB::table('information_pages')
            ->where('slug', 'contact')
            ->first(['id', 'body_html']);

        if (!$contactPage) {
            return;
        }

        DB::table('information_pages')
            ->where('id', $contactPage->id)
            ->update([
                'contact_address' => InformationPage::extractContactAddressFromHtml($contactPage->body_html)
                    ?? InformationPage::DEFAULT_CONTACT_ADDRESS,
                'contact_email' => InformationPage::extractContactEmailFromHtml($contactPage->body_html)
                    ?? InformationPage::DEFAULT_CONTACT_EMAIL,
                'contact_hours' => InformationPage::extractContactHoursFromHtml($contactPage->body_html)
                    ?? InformationPage::DEFAULT_CONTACT_HOURS,
            ]);
    }

    public function down(): void
    {
        Schema::table('information_pages', function (Blueprint $table) {
            $table->dropColumn(['contact_address', 'contact_email', 'contact_hours']);
        });
    }
};
