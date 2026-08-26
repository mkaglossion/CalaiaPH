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

        $this->get(route('our-story'))
            ->assertSee('Our Story: Respect and Connect')
            ->assertSeeText('At Calaia, we believe in a world where nature, farming, and community thrive in perfect harmony')
            ->assertSee('regenerative land stewardship')
            ->assertSee('<strong class="font-bold text-gray-900">Calaia</strong>', false)
            ->assertSee('<strong class="font-bold text-gray-900">living sanctuary</strong>', false)
            ->assertSee('<strong class="font-bold text-gray-900">regenerative land stewardship</strong>', false)
            ->assertSee('<strong class="font-bold text-gray-900">biodynamic farming and forest regeneration</strong>', false)
            ->assertSee('<strong class="font-bold text-gray-900">unfiltered outdoor living</strong>', false)
            ->assertSee('<strong class="font-bold text-gray-900">ethical food production</strong>', false)
            ->assertSee('<strong class="font-bold text-gray-900">authenticity and simplicity</strong>', false)
            ->assertSee('Calaia is a <strong class="font-bold text-gray-900">safe and inclusive environment</strong>', false)
            ->assertDontSee('<strong class="font-bold text-gray-900">a safe and inclusive environment</strong>', false)
            ->assertDontSee('<strong class="font-bold text-gray-900">well-being</strong>', false)
            ->assertSee('<strong class="font-bold text-gray-900">humane animal care</strong>', false)
            ->assertSee('The Beginning')
            ->assertSee('Our Journey')
            ->assertSee('Mission & Vision', false)
            ->assertSee('Our Values')
            ->assertSee('Our Impact')
            ->assertSee('Looking Forward')
            ->assertSee('Join Our Growing Community');
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
        $indexResponse->assertSee('At &lt;strong class=&quot;font-bold text-gray-900&quot;&gt;Calaia&lt;/strong&gt;, we believe in a world where nature, farming, and community thrive in perfect harmony', false);
        $indexResponse->assertSee('The Beginning');
        $indexResponse->assertSee('Our Journey');
        $indexResponse->assertSee('data-lucide="info"', false);
        $indexResponse->assertSeeInOrder(['Shop', 'Information', 'Policies &amp; Terms'], false);
        $indexResponse->assertDontSee(route('admin.information.index', ['page' => 'sustainability']), false);
        $indexResponse->assertDontSee(route('admin.information.index', ['page' => 'our-services']), false);

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

    public function test_hidden_information_links_remain_accessible_but_do_not_appear_in_navigation(): void
    {
        $this->get(route('sustainability'))->assertOk();
        $this->get(route('our-services'))->assertOk();

        $footerResponse = $this->get(route('our-story'));
        $footerResponse->assertOk();
        $footerResponse->assertDontSee('href="'.route('sustainability').'"', false);
        $footerResponse->assertDontSee('href="'.route('our-services').'"', false);

        $adminItems = collect(InformationPage::adminNavigationItems());
        $this->assertFalse($adminItems->contains('params.page', 'sustainability'));
        $this->assertFalse($adminItems->contains('params.page', 'our-services'));
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
            ->assertSee('Shared contact details')
            ->assertSee('name="contact_address"', false)
            ->assertSee('name="contact_phone"', false)
            ->assertSee('name="contact_email"', false)
            ->assertSee('name="contact_hours"', false)
            ->assertSee('>Business hours</span>', false)
            ->assertSee('value="Open Daily 8:00 AM – 6:00 PM"', false)
            ->assertSee('{{contact_form}}')
            ->assertSee('{{contact_map}}');

        $contactPhone = '+63 917 555 0123';
        $contactAddress = 'Dynamic Farm Address, Calaca, Batangas';
        $contactEmail = 'contact@calaia.test';
        $contactHoursValue = '7:00 AM – 7:00 PM';
        $contactHours = 'Open Daily '.$contactHoursValue;
        $this->actingAs($admin)
            ->put(route('admin.information.update', $contactPage->slug), [
                'nav_label' => 'Contact Us',
                'meta_title' => 'Contact Calaia Farm',
                'meta_description' => 'Contact page metadata.',
                'meta_keywords' => 'contact, CalaiaPH',
                'hero_eyebrow' => null,
                'hero_title' => 'Talk to Our Team',
                'hero_description' => 'We are ready to help.',
                'contact_address' => $contactAddress,
                'contact_phone' => $contactPhone,
                'contact_email' => $contactEmail,
                'contact_hours' => $contactHours,
                'body_html' => '<section><h2>Dynamic contact details</h2><span>Address</span><address>Old address</address><span>Phone</span><a href="tel:+639051294741">+63 905 129 4741</a><span>Email</span><a href="mailto:old@example.com">old@example.com</a><i class="fa fa-clock"><font><span class="font-medium">Open Daily</span><span>9:00 AM – 5:00 PM</span></font></i>{{contact_form}}{{contact_map}}</section>',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.information.index', ['page' => 'contact']));

        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('Dynamic contact details');
        $response->assertSee($contactPhone);
        $response->assertSee($contactAddress);
        $response->assertSee($contactEmail);
        $response->assertDontSee('<strong>Open Daily</strong>', false);
        $response->assertSee($contactHours);
        $response->assertSee('style="font-weight: 400 !important;"', false);
        $response->assertDontSee('fa-clock"><font', false);
        $response->assertSee($contactHoursValue);
        $response->assertDontSee('9:00 AM – 5:00 PM');
        $this->assertSame(1, substr_count($response->getContent(), $contactHoursValue));
        $response->assertSee('href="tel:+639175550123"', false);
        $response->assertSee('href="mailto:contact@calaia.test"', false);
        $response->assertDontSee('+63 905 129 4741');
        $response->assertSee('action="'.route('contact.submit').'"', false);
        $response->assertSee('https://www.google.com/maps?q=Calaca%20Nature%20Park&output=embed', false);
        $response->assertDontSee('{{contact_form}}');
        $response->assertDontSee('{{contact_map}}');

        $footer = view('components.footer')->render();
        $this->assertStringContainsString('lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)]', $footer);
        $this->assertStringContainsString('href="'.route('contact').'"', $footer);
        $this->assertStringContainsString('Contact Us', $footer);
        $this->assertStringContainsString($contactEmail, $footer);
        $this->assertStringNotContainsString('<span>CONTACT</span>', $footer);
        $this->assertStringNotContainsString($contactPhone, $footer);
        $this->assertStringNotContainsString($contactAddress, $footer);
        $this->assertStringNotContainsString($contactHours, $footer);
        $this->assertStringNotContainsString('href="tel:+639175550123"', $footer);

        $maintenance = view('system.maintenance', [
            'message' => 'Scheduled maintenance.',
            'logo' => null,
            'favicon' => null,
            'contactPhone' => InformationPage::contactPhone(),
            'contactPhoneUri' => InformationPage::contactPhoneUri(),
            'contactEmail' => InformationPage::contactDetails()['email'],
        ])->render();
        $this->assertStringContainsString($contactPhone, $maintenance);
        $this->assertStringContainsString($contactEmail, $maintenance);
        $this->assertStringContainsString('href="tel:+639175550123"', $maintenance);
        $this->assertStringNotContainsString('+63 956-319-2448', $maintenance);
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

    public function test_editing_the_visible_contact_phone_synchronizes_the_shared_maintenance_phone(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $contactPage = InformationPage::query()->where('slug', 'contact')->firstOrFail();
        $currentPhone = $contactPage->resolvedContactPhone();
        $updatedPhone = '+63 956 319 2448';

        $this->actingAs($admin)
            ->put(route('admin.information.update', $contactPage->slug), [
                'nav_label' => $contactPage->nav_label,
                'meta_title' => $contactPage->meta_title,
                'meta_description' => $contactPage->meta_description,
                'meta_keywords' => $contactPage->meta_keywords,
                'hero_eyebrow' => $contactPage->hero_eyebrow,
                'hero_title' => $contactPage->hero_title,
                'hero_description' => $contactPage->hero_description,
                'contact_address' => $contactPage->resolvedContactAddress(),
                'contact_phone' => $currentPhone,
                'contact_email' => $contactPage->resolvedContactEmail(),
                'contact_hours' => $contactPage->resolvedContactHours(),
                'body_html' => '<section><span>'.$updatedPhone.'</span>{{contact_form}}{{contact_map}}</section>',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.information.index', ['page' => 'contact']));

        $this->assertDatabaseHas('information_pages', [
            'slug' => 'contact',
            'contact_phone' => $updatedPhone,
        ]);

        $contactDetails = InformationPage::contactDetails();
        $this->assertSame($updatedPhone, $contactDetails['phone']);
        $this->assertSame('tel:+639563192448', $contactDetails['phone_uri']);

        $maintenance = view('system.maintenance', [
            'message' => 'Scheduled maintenance.',
            'logo' => null,
            'favicon' => null,
            'contactPhone' => $contactDetails['phone'],
            'contactPhoneUri' => $contactDetails['phone_uri'],
            'contactEmail' => $contactDetails['email'],
        ])->render();
        $this->assertStringContainsString($updatedPhone, $maintenance);
        $this->assertStringContainsString('href="tel:+639563192448"', $maintenance);
        $this->assertStringNotContainsString($currentPhone, $maintenance);
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
