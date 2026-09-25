<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $contactPhone = Schema::hasTable('information_pages')
            ? trim((string) DB::table('information_pages')->where('slug', 'contact')->value('contact_phone'))
            : '';

        if ($contactPhone !== '' && Schema::hasTable('policy_pages') && Schema::hasColumn('policy_pages', 'body_html')) {
            DB::table('policy_pages')
                ->whereIn('slug', ['privacy-notice', 'calaia-policy', 'cookies-policy', 'terms-service'])
                ->where(function ($query) {
                    $query->where('body_html', 'like', '%+63 949 139 4912%')
                        ->orWhere('body_html', 'like', '%+63 905 129 4741%');
                })
                ->update([
                    'body_html' => DB::raw("REPLACE(REPLACE(body_html, '+63 949 139 4912', ".DB::getPdo()->quote($contactPhone)."), '+63 905 129 4741', ".DB::getPdo()->quote($contactPhone).")"),
                    'updated_at' => now(),
                ]);
        }

        $this->replaceFarmTypo('farm_experiences', 'desc');
        $this->replaceFarmTypo('farm_texts', 'text');
        $this->replaceFarmTypo('farm_page_sections', 'description');
        $this->replaceFarmTypo('farm_page_sections', 'items');
    }

    public function down(): void
    {
        // The correction is intentionally not reversed because the original
        // content may have been edited after deployment.
    }

    private function replaceFarmTypo(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $quotedColumn = DB::getQueryGrammar()->wrap($column);

        DB::table($table)
            ->where($column, 'like', '%active roll%')
            ->update([
                $column => DB::raw("REPLACE({$quotedColumn}, 'active roll', 'active role')"),
                'updated_at' => now(),
            ]);
    }
};