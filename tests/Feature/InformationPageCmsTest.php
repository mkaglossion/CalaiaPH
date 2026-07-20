<?php

namespace Tests\Feature;

use App\Models\InformationPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformationPageCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_information_pages_are_seeded_and_existing_content_is_publicly_rendered(): void
    {
        $this->assertDatabaseCount('information_pages', 4);

        foreach (config('information_pages.pages') as $slug => $defaults) {
            $response = $this->get(route($defaults['route']));

            $response->assertOk();
            $response->assertSee($defaults['hero_title']);
            $response->assertSee($defaults['nav_label']);
            $this->assertSame(
                1,
                substr_count($response->getContent(), '<section class="relative h-screen'),
                "The {$slug} page should render exactly one hero section."
            );
            $this->assertDatabaseHas('information_pages', [
                'slug' => $slug,
                'body_html' => null,
                'is_published' => true,
            ]);
        }

        $this->get(route('our-story'))->assertSee('The Beginning');
        $this->get(route('sustainability'))->assertSee('Our Environmental Commitment');
        $this->get(route('our-services'))->assertSee('Accommodation Services');
        $this->get(route('contact'))->assertSee('Visit or Contact Us');
    }

    public function test_admin_can_manage_information_content_and_footer_label(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);
        $informationPage = InformationPage::query()->where('slug', 'our-story')->firstOrFail();

        $indexResponse = $this->actingAs($admin)->get(route('admin.information.index', [
            'page' => $informationPage->slug,
        ]));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Information CMS');
        $indexResponse->assertSee('Full page content');
        $indexResponse->assertSee('The Beginning');
        $indexResponse->assertSee('data-lucide="info"', false);
        $indexResponse->assertSeeInOrder(['Shop', 'Information', 'Policies &amp; Terms'], false);

        $updateResponse = $this->actingAs($admin)->put(route('admin.information.update', $informationPage->slug), [
            'nav_label' => 'Our Journey',
            'meta_title' => 'Our Journey - Calaia Farm',
            'meta_description' => 'Updated story metadata.',
            'meta_keywords' => 'story, CalaiaPH',
            'hero_eyebrow' => 'Since 2018',
            'hero_title' => 'A Dynamic Story',
            'hero_description' => 'Managed from the Information CMS.',
            'body_html' => '<section><h2>CMS-managed story content</h2><p onclick="alert(1)">Safe paragraph.</p><a href="javascript:alert(1)">Unsafe link</a><script>alert(1)</script></section>',
            'is_published' => '1',
        ]);

        $updateResponse->assertRedirect(route('admin.information.index', [
            'page' => $informationPage->slug,
        ]));

        $informationPage->refresh();
        $this->assertSame('Our Journey', $informationPage->nav_label);
        $this->assertStringContainsString('CMS-managed story content', $informationPage->body_html);
        $this->assertStringNotContainsString('<script', $informationPage->body_html);
        $this->assertStringNotContainsString('onclick', $informationPage->body_html);
        $this->assertStringNotContainsString('javascript:', $informationPage->body_html);
        $this->assertSame(
            'Our Journey',
            collect(InformationPage::adminNavigationItems())->firstWhere('params.page', 'our-story')['label']
        );

        $publicResponse = $this->get(route('our-story'));
        $publicResponse->assertOk();
        $publicResponse->assertSee('A Dynamic Story');
        $publicResponse->assertSee('CMS-managed story content');
        $publicResponse->assertSee('Our Journey');
        $publicResponse->assertDontSee('onclick="alert(1)"', false);
        $publicResponse->assertDontSee('javascript:alert(1)', false);
        $publicResponse->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_contact_placeholders_keep_the_working_form_and_map(): void
    {
        $admin = User::factory()->create([
            'user_type' => 'admin',
        ]);
        $contactPage = InformationPage::query()->where('slug', 'contact')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.information.index', ['page' => 'contact']))
            ->assertOk()
            ->assertSee('{{contact_form}}')
            ->assertSee('{{contact_map}}');

        $this->actingAs($admin)
            ->put(route('admin.information.update', $contactPage->slug), [
                'nav_label' => 'Contact Us',
                'meta_title' => 'Contact Calaia Farm',
                'meta_description' => 'Contact page metadata.',
                'meta_keywords' => 'contact, CalaiaPH',
                'hero_eyebrow' => null,
                'hero_title' => 'Talk to Our Team',
                'hero_description' => 'We are ready to help.',
                'body_html' => '<section><h2>Dynamic contact details</h2>{{contact_form}}{{contact_map}}</section>',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.information.index', ['page' => 'contact']));

        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('Dynamic contact details');
        $response->assertSee('action="'.route('contact.submit').'"', false);
        $response->assertSee('https://www.google.com/maps?q=Calaca%20Nature%20Park&output=embed', false);
        $response->assertDontSee('{{contact_form}}');
        $response->assertDontSee('{{contact_map}}');
    }

    public function test_unpublished_information_page_returns_not_found_and_is_removed_from_footer(): void
    {
        InformationPage::query()
            ->where('slug', 'sustainability')
            ->update(['is_published' => false]);

        $this->get(route('sustainability'))->assertNotFound();

        $response = $this->get(route('our-story'));
        $response->assertOk();
        $response->assertDontSee('href="'.route('sustainability').'"', false);
    }

    public function test_non_admin_cannot_open_information_cms(): void
    {
        $customer = User::factory()->create([
            'user_type' => 'client',
        ]);

        $this->actingAs($customer)
            ->get(route('admin.information.index'))
            ->assertForbidden();
    }
}
