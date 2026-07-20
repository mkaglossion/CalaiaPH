<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $missing = [
            'footer_social_heading' => ! Schema::hasColumn('system_settings', 'footer_social_heading'),
            'footer_instagram_url' => ! Schema::hasColumn('system_settings', 'footer_instagram_url'),
            'footer_facebook_url' => ! Schema::hasColumn('system_settings', 'footer_facebook_url'),
            'footer_messenger_url' => ! Schema::hasColumn('system_settings', 'footer_messenger_url'),
            'footer_email' => ! Schema::hasColumn('system_settings', 'footer_email'),
        ];

        if (in_array(true, $missing, true)) {
            Schema::table('system_settings', function (Blueprint $table) use ($missing): void {
                if ($missing['footer_social_heading']) {
                    $table->string('footer_social_heading', 160)->nullable();
                }
                if ($missing['footer_instagram_url']) {
                    $table->text('footer_instagram_url')->nullable();
                }
                if ($missing['footer_facebook_url']) {
                    $table->text('footer_facebook_url')->nullable();
                }
                if ($missing['footer_messenger_url']) {
                    $table->text('footer_messenger_url')->nullable();
                }
                if ($missing['footer_email']) {
                    $table->string('footer_email')->nullable();
                }
            });
        }

        DB::table('system_settings')->update([
            'footer_social_heading' => 'Get connected with us on social networks:',
            'footer_instagram_url' => 'https://www.instagram.com/calaia.ph/',
            'footer_facebook_url' => 'https://www.facebook.com/calaia.ph/',
            'footer_messenger_url' => 'https://m.me/410774518796277',
            'footer_email' => 'calaia.ph@gmail.com',
        ]);
    }

    public function down(): void
    {
        $columns = collect([
            'footer_social_heading',
            'footer_instagram_url',
            'footer_facebook_url',
            'footer_messenger_url',
            'footer_email',
        ])->filter(fn (string $column): bool => Schema::hasColumn('system_settings', $column))->all();

        if ($columns !== []) {
            Schema::table('system_settings', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
