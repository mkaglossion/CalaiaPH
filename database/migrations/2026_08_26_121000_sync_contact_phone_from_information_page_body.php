<?php

use App\Models\InformationPage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $contactPage = DB::table('information_pages')
            ->where('slug', 'contact')
            ->first(['id', 'body_html']);

        if (!$contactPage) {
            return;
        }

        $phone = InformationPage::extractContactPhoneFromHtml($contactPage->body_html);

        if ($phone !== null) {
            DB::table('information_pages')
                ->where('id', $contactPage->id)
                ->update(['contact_phone' => $phone]);
        }
    }

    public function down(): void
    {
        // Data synchronization is intentionally not reversed.
    }
};
