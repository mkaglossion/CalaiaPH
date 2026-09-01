<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InformationPage;
use App\Services\PolicyContentSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InformationPageController extends Controller
{
    public function index(Request $request): View
    {
        $pages = InformationPage::ensureDefaults()
            ->filter(static fn (InformationPage $page): bool => InformationPage::isVisibleInNavigation($page->slug))
            ->values();
        $requestedSlug = (string) $request->query('page', $pages->first()?->slug);
        $policyPage = $pages->firstWhere('slug', $requestedSlug) ?? $pages->firstOrFail();

        return view('admin.policies.index', [
            'pages' => $pages,
            'policyPage' => $policyPage,
            'editableBodyHtml' => $policyPage->editableBodyHtml(),
            'cmsTitle' => 'Information CMS',
            'cmsDescription' => 'Manage footer labels, SEO, hero visibility and copy, publication status, and the complete content for every public information page.',
            'cmsNavLabel' => 'Information pages',
            'configNamespace' => 'information_pages',
            'adminIndexRoute' => 'admin.information.index',
            'adminUpdateRoute' => 'admin.information.update',
            'adminHeroVisibilityRoute' => 'admin.information.hero-visibility.update',
            'supportsLastUpdated' => false,
            'bodyHelpText' => 'Edit visually or switch to HTML source for precise layout changes. Keep the contact form and map placeholders in the Contact Us page.',
        ]);
    }

    public function update(
        Request $request,
        InformationPage $informationPage,
        PolicyContentSanitizer $sanitizer
    ): RedirectResponse {
        $rules = [
            'nav_label' => ['required', 'string', 'max:80'],
            'meta_title' => ['required', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'string', 'max:1000'],
            'hero_eyebrow' => ['nullable', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string', 'max:1000'],
            'is_hero_enabled' => ['required', 'boolean'],
            'body_html' => ['required', 'string', 'max:1000000'],
            'is_published' => ['required', 'boolean'],
        ];

        if ($informationPage->slug === 'contact') {
            $rules['contact_address'] = ['required', 'string', 'max:500'];
            $rules['contact_phone'] = ['required', 'string', 'max:30', 'regex:/^[0-9+().\-\s]+$/'];
            $rules['contact_email'] = ['required', 'email:rfc', 'max:255'];
            $rules['contact_hours'] = ['required', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        if ($informationPage->slug === 'contact') {
            $validated['contact_address'] = $this->synchronizedContactValue(
                (string) $validated['contact_address'],
                $informationPage->resolvedContactAddress(),
                InformationPage::extractContactAddressFromHtml($validated['body_html'])
            );

            $currentPhone = $informationPage->resolvedContactPhone();
            $fieldPhone = trim((string) $validated['contact_phone']);
            $bodyPhone = InformationPage::extractContactPhoneFromHtml($validated['body_html']);

            // The dedicated field wins when it was changed. Otherwise, keep
            // legacy visual-editor updates working by syncing the phone link.
            if ($this->samePhone($fieldPhone, $currentPhone) && $bodyPhone !== null) {
                $validated['contact_phone'] = $bodyPhone;
            } else {
                $validated['contact_phone'] = $fieldPhone;
            }

            $validated['contact_email'] = $this->synchronizedContactValue(
                (string) $validated['contact_email'],
                $informationPage->resolvedContactEmail(),
                InformationPage::extractContactEmailFromHtml($validated['body_html']),
                caseInsensitive: true
            );
            $bodyHours = InformationPage::extractContactHoursFromHtml($validated['body_html']);
            $hoursValue = $this->synchronizedContactValue(
                InformationPage::contactHoursValue((string) $validated['contact_hours']),
                $informationPage->resolvedContactHoursValue(),
                $bodyHours !== null ? InformationPage::contactHoursValue($bodyHours) : null
            );
            $validated['contact_hours'] = InformationPage::formatContactHours($hoursValue);
        }

        $validated['body_html'] = $sanitizer->sanitize($validated['body_html']);

        if ($validated['body_html'] === '') {
            return back()->withInput()->withErrors([
                'body_html' => 'The page content cannot be empty after unsafe markup is removed.',
            ]);
        }

        $validated['updated_by'] = $request->user()->id;
        $informationPage->update($validated);

        return redirect()
            ->route('admin.information.index', ['page' => $informationPage->slug])
            ->with('success', $informationPage->nav_label.' was updated successfully.');
    }

    public function updateHeroVisibility(Request $request, InformationPage $informationPage): JsonResponse
    {
        $validated = $request->validate([
            'is_hero_enabled' => ['required', 'boolean'],
        ]);

        $informationPage->update([
            'is_hero_enabled' => $validated['is_hero_enabled'],
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'is_hero_enabled' => $informationPage->fresh()->is_hero_enabled,
        ]);
    }

    private function samePhone(string $left, string $right): bool
    {
        $normalize = static fn (string $phone): string => preg_replace('/\D/', '', $phone) ?? '';

        return $normalize($left) === $normalize($right);
    }

    private function synchronizedContactValue(
        string $fieldValue,
        string $currentValue,
        ?string $bodyValue,
        bool $caseInsensitive = false
    ): string {
        $fieldValue = trim($fieldValue);
        $currentValue = trim($currentValue);
        $unchanged = $caseInsensitive
            ? strcasecmp($fieldValue, $currentValue) === 0
            : $fieldValue === $currentValue;

        return $unchanged && $bodyValue !== null ? trim($bodyValue) : $fieldValue;
    }
}
