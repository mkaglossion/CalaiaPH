<?php

namespace Tests\Feature;

use App\Models\PolicyPage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyPageCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_pages_are_seeded_and_publicly_rendered(): void
    {
        $this->assertDatabaseCount('policy_pages', 5);

        foreach (config('policy_pages.pages') as $slug => $defaults) {
            $response = $this->get(route($defaults['route']));

            $response->assertOk();
            $response->assertSee($defaults['hero_title']);
            $response->assertSee($defaults['nav_label']);
            $this->assertSame(
                1,
                substr_count($response->getContent(), '<section class="relative h-screen'),
                "The {$slug} page should render exactly one hero section."
            );
            $this->assertDatabaseHas('policy_pages', [
                'slug' => $slug,
                'is_published' => true,
            ]);
        }
    }

    public function test_admin_can_manage_policy_content_and_unsafe_markup_is_removed(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);
        $policyPage = PolicyPage::query()->where('slug', 'privacy-notice')->firstOrFail();

        $indexResponse = $this->actingAs($admin)->get(route('admin.policies.index', [
            'page' => $policyPage->slug,
        ]));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Policies &amp; Terms CMS', false);
        $indexResponse->assertSee('Full page content');
        $indexResponse->assertSee('data-lucide="file-lock-2"', false);
        $indexResponse->assertSeeInOrder(['Shop', 'Policies &amp; Terms', 'Booking Confirmation'], false);
        $indexResponse->assertDontSee(route('admin.policies.index', ['page' => 'data-deletion']), false);
        $indexResponse->assertDontSee(route('admin.policies.index', ['page' => 'cookies-policy']), false);

        $updateResponse = $this->actingAs($admin)->put(route('admin.policies.update', $policyPage->slug), [
            'nav_label' => 'Privacy Center',
            'meta_title' => 'Privacy Center - Calaia Farm',
            'meta_description' => 'Updated privacy metadata.',
            'meta_keywords' => 'privacy, CalaiaPH',
            'hero_eyebrow' => 'Your Data',
            'hero_title' => 'Our Privacy Center',
            'hero_description' => 'A fully dynamic privacy page.',
            'last_updated' => '2026-07-17',
            'body_html' => '<section><h2>CMS-managed privacy content</h2><p onclick="alert(1)">Safe paragraph.</p><a href="javascript:alert(1)">Unsafe link</a><script>alert(1)</script></section>',
            'is_published' => '1',
        ]);

        $updateResponse->assertRedirect(route('admin.policies.index', [
            'page' => $policyPage->slug,
        ]));

        $policyPage->refresh();
        $this->assertSame('Privacy Center', $policyPage->nav_label);
        $this->assertStringContainsString('CMS-managed privacy content', $policyPage->body_html);
        $this->assertStringNotContainsString('<script', $policyPage->body_html);
        $this->assertStringNotContainsString('onclick', $policyPage->body_html);
        $this->assertStringNotContainsString('javascript:', $policyPage->body_html);
        $this->assertSame(
            'Privacy Center',
            collect(PolicyPage::adminNavigationItems())->firstWhere('params.page', 'privacy-notice')['label']
        );

        $publicResponse = $this->get(route('privacy-notice'));
        $publicResponse->assertOk();
        $publicResponse->assertSee('Our Privacy Center');
        $publicResponse->assertSee('CMS-managed privacy content');
        $publicResponse->assertSee('Privacy Center');
        $publicResponse->assertDontSee('onclick="alert(1)"', false);
        $publicResponse->assertDontSee('javascript:alert(1)', false);
        $publicResponse->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_unpublished_policy_page_returns_not_found_and_is_removed_from_footer(): void
    {
        PolicyPage::query()
            ->where('slug', 'data-deletion')
            ->update(['is_published' => false]);

        $this->get(route('data-deletion'))->assertNotFound();

        $response = $this->get(route('privacy-notice'));
        $response->assertOk();
        $response->assertDontSee('Data Deletion');
    }

    public function test_hidden_policy_links_remain_available_but_do_not_appear_in_the_footer(): void
    {
        $this->get(route('data-deletion'))->assertOk();
        $this->get(route('cookies-policy'))->assertOk();

        $footer = view('components.footer')->render();
        $this->assertStringNotContainsString('href="'.route('data-deletion').'"', $footer);
        $this->assertStringNotContainsString('href="'.route('cookies-policy').'"', $footer);
        $this->assertStringContainsString('href="'.route('privacy-notice').'"', $footer);
        $this->assertStringContainsString('href="'.route('calaia-policy').'"', $footer);
        $this->assertStringContainsString('href="'.route('terms-service').'"', $footer);

        $adminItems = collect(PolicyPage::adminNavigationItems());
        $this->assertFalse($adminItems->contains('params.page', 'data-deletion'));
        $this->assertFalse($adminItems->contains('params.page', 'cookies-policy'));
    }

    public function test_every_policy_page_content_field_is_database_driven(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);

        foreach (config('policy_pages.pages') as $index => $defaults) {
            $slug = is_string($index) ? $index : $defaults['slug'];
            $date = Carbon::parse('2026-08-01')->addDays(array_search($slug, array_keys(config('policy_pages.pages')), true));
            $identifier = str_replace('-', ' ', $slug);

            $this->actingAs($admin)
                ->put(route('admin.policies.update', $slug), [
                    'nav_label' => "Dynamic {$identifier} navigation",
                    'meta_title' => "Dynamic {$identifier} meta title",
                    'meta_description' => "Dynamic {$identifier} meta description",
                    'meta_keywords' => "dynamic, {$identifier}",
                    'hero_eyebrow' => "Dynamic {$identifier} eyebrow",
                    'hero_title' => "Dynamic {$identifier} hero",
                    'hero_description' => "Dynamic {$identifier} description",
                    'last_updated' => $date->toDateString(),
                    'body_html' => "<section><h2>Dynamic {$identifier} body</h2><p>Updated {{last_updated}}</p></section>",
                    'is_published' => '1',
                ])
                ->assertRedirect(route('admin.policies.index', ['page' => $slug]));

            $response = $this->get(route($defaults['route']));

            $response->assertOk();
            $response->assertSee("Dynamic {$identifier} meta title");
            $response->assertSee("Dynamic {$identifier} eyebrow");
            $response->assertSee("Dynamic {$identifier} hero");
            $response->assertSee("Dynamic {$identifier} description");
            $response->assertSee("Dynamic {$identifier} body");
            $response->assertSee("Dynamic {$identifier} navigation");
            $response->assertSee($date->format('F j, Y'));
        }
    }

    public function test_non_admin_cannot_open_policy_cms(): void
    {
        $customer = User::factory()->create([
            'user_type' => 'client',
        ]);

        $this->actingAs($customer)
            ->get(route('admin.policies.index'))
            ->assertForbidden();
    }
}
