<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        if (Schema::hasTable('information_pages')) {
            DB::table('information_pages')
                ->where('slug', 'our-story')
                ->update([
                    'meta_title' => 'Our Story - Calaia Farm',
                    'meta_description' => 'Discover the inspiring journey of Calaia Farm from a simple dream to a thriving nature sanctuary committed to sustainable agriculture and community development.',
                    'hero_eyebrow' => 'Est. 2018 - Calaca, Batangas',
                    'hero_title' => 'Our Story',
                    'hero_description' => "From a simple dream to a thriving sanctuary dedicated to\nsustainability, community & nature",
                    // Keep the original page structure and use its editable CMS default.
                    'body_html' => null,
                    'updated_at' => $now,
                ]);
        }

        if (Schema::hasTable('staycation_page_texts')) {
            $this->upsertText('staycation_page_texts', 'staycation_page_label', 'Staycation Options', $now);
            $this->upsertText('staycation_page_texts', 'staycation_page_title', 'All Glamping & Camping', $now);
            $this->upsertText(
                'staycation_page_texts',
                'staycation_page_intro',
                'Choose from our range of glamping tents and camping options. Each option has unique features that suit your needs and tastes.',
                $now
            );
        }

        if (Schema::hasTable('staycations') && Schema::hasTable('staycation_texts')) {
            $rooms = [
                'Premium Glamping' => [
                    'description' => 'Ultimate relaxation for all ages.',
                    'features' => [
                        'Air-conditioned 25sqm tent with 4 beds.',
                        'Exclusive toilet and heated shower located beside the tent.',
                        'Bathroom amenities: bath and face towels, body soap, shampoo, dental, and vanity kits.',
                        'Complimentary breakfast for registered guests.',
                        'Complimentary farm tour and animal feeding for all registered guests.',
                        'En suite personal refrigerator',
                        'Convenience outlets in tent',
                        'Access to WiFi, camp amenities, and activities',
                    ],
                ],
                'Standard Glamping' => [
                    'description' => 'Cozy details with your comfort in mind.',
                    'features' => [
                        'Air-conditioned 25sqm tent with 4 beds',
                        'Exclusive toilet and heated shower located a few steps away from the tent.',
                        'Bathroom amenities: bath towels, body soap, shampoo, dental, and vanity kits',
                        'Complimentary breakfast for all registered guests',
                        'Complimentary farm tour and animal feeding for all registered guests',
                        'Convenience outlets in tent',
                        'Access to WiFi, camp amenities, and activities',
                    ],
                ],
                'Fan Tent' => [
                    'description' => 'Stay close to nature in a peaceful setting.',
                    'features' => [
                        'Fan-cooled 18 sqm tent good for 4 guests',
                        '4 bunk-style beds',
                        'Use of common toilet and shower',
                        'Convenience outlet in tent',
                        'Access to WiFi, camp amenities, and activities',
                    ],
                ],
                'BYOT' => [
                    'description' => 'Your gear, your way.',
                    'features' => [
                        'Spacious area for your tent setup',
                        'Use of common toilet and shower',
                        'Provision for electricity source',
                        'Access to WiFi, camp amenities, and activities',
                    ],
                ],
            ];

            foreach ($rooms as $roomName => $copy) {
                $staycationId = DB::table('staycations')->where('name', $roomName)->value('id');
                if (! $staycationId) {
                    continue;
                }

                DB::table('staycation_texts')->updateOrInsert(
                    ['staycation_id' => $staycationId, 'section' => 'description'],
                    [
                        'title' => 'Room Description',
                        'content' => $copy['description'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                DB::table('staycation_texts')->updateOrInsert(
                    ['staycation_id' => $staycationId, 'section' => 'features'],
                    [
                        'title' => 'Features',
                        'content' => implode("\n", $copy['features']),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        if (Schema::hasTable('events')) {
            DB::table('events')->where('name', 'Birthday Celebration')->update([
                'description' => 'Celebrate your special day with our fun farm activities and delicious food.',
                'features' => 'Event coordination*, Beautiful farm setting, Curated activities',
                'updated_at' => $now,
            ]);
            DB::table('events')->where('name', 'Corporate Team Building')->update([
                'description' => 'Enhance team collaboration with our outdoor workshops.',
                'features' => 'Need-based workshops, Full food menus, Event coordination*',
                'updated_at' => $now,
            ]);
            DB::table('events')->whereIn('name', ['Wedding Reception', 'Wedding Receptions'])->update([
                'name' => 'Wedding Receptions',
                'description' => 'Celebrate your special day in a special outdoor venue.',
                'features' => 'Event coordination*, Supplier support',
                'updated_at' => $now,
            ]);
        }

        if (Schema::hasTable('farm_texts')) {
            $this->upsertText('farm_texts', 'farm_cta_title', 'Ready for your farm experience?', $now);
        }

        if (Schema::hasTable('farm_page_sections')) {
            DB::table('farm_page_sections')
                ->whereIn('key', ['ethical_livestock', 'product_cards', 'field_banners'])
                ->update(['is_active' => false, 'updated_at' => $now]);

            $existingNature = DB::table('farm_page_sections')->where('key', 'growing_nature')->first();
            $existingItems = $existingNature ? json_decode((string) $existingNature->items, true) : [];
            $existingItems = is_array($existingItems) ? array_values($existingItems) : [];

            $natureItems = [
                $this->farmItem('soil-first', 'Soil First', 'We focus on composting, covering, and zero tillage.', $existingItems[0] ?? []),
                $this->farmItem('ethical-livestock-management', 'Ethical Livestock Management', 'All our animals thrive in low-stress environments, and have access to wide open spaces.', $existingItems[1] ?? []),
                $this->farmItem('respect-for-natural-cycles', 'Respect for Natural Cycles', 'We allow nature’s rhythms to guide our farming processes.', $existingItems[2] ?? []),
                $this->farmItem('forest-regeneration', 'Forest Regeneration', 'With your help, we have planted over 200 local and endemic trees to support a self-sustaining ecosystem.'),
            ];

            DB::table('farm_page_sections')->where('key', 'growing_nature')->update([
                'title' => 'Nurtured Ecosystems',
                'description' => null,
                'items' => json_encode($natureItems, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'sort_order' => 20,
                'updated_at' => $now,
            ]);

            DB::table('farm_page_sections')->where('key', 'farm_experiences')->update([
                'title' => 'Life on the Farm',
                'description' => null,
                'is_active' => true,
                'sort_order' => 10,
                'updated_at' => $now,
            ]);
        }

        if (Schema::hasTable('farm_experiences')) {
            $titles = [
                'Interactive Animal Encounters',
                'Seasonal Pickings',
                'Learning & Discovery',
            ];

            $experienceIds = DB::table('farm_experiences')->orderBy('sort_order')->orderBy('id')->limit(3)->pluck('id');
            foreach ($experienceIds as $index => $experienceId) {
                DB::table('farm_experiences')->where('id', $experienceId)->update([
                    'title' => $titles[$index],
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // These are approved CMS content revisions and should not overwrite later editor changes.
    }

    private function upsertText(string $table, string $label, string $text, mixed $now): void
    {
        DB::table($table)->updateOrInsert(
            ['label' => $label],
            ['text' => $text, 'created_at' => $now, 'updated_at' => $now]
        );
    }

    private function farmItem(string $key, string $title, string $description, array $existing = []): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'text' => null,
            'description' => $description,
            'meta' => null,
            'alignment' => 'left',
            'image_url' => $existing['image_url'] ?? null,
            'image_public_id' => $existing['image_public_id'] ?? null,
        ];
    }
};
