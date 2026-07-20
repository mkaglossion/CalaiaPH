<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InformationPage;
use App\Services\PolicyContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InformationPageController extends Controller
{
    public function index(Request $request): View
    {
        $pages = InformationPage::ensureDefaults();
        $requestedSlug = (string) $request->query('page', $pages->first()?->slug);
        $policyPage = $pages->firstWhere('slug', $requestedSlug) ?? $pages->firstOrFail();

        return view('admin.policies.index', [
            'pages' => $pages,
            'policyPage' => $policyPage,
            'editableBodyHtml' => $policyPage->editableBodyHtml(),
            'cmsTitle' => 'Information CMS',
            'cmsDescription' => 'Manage footer labels, SEO, hero copy, publication status, and the complete content for every public information page.',
            'cmsNavLabel' => 'Information pages',
            'configNamespace' => 'information_pages',
            'adminIndexRoute' => 'admin.information.index',
            'adminUpdateRoute' => 'admin.information.update',
            'supportsLastUpdated' => false,
            'bodyHelpText' => 'Edit visually or switch to HTML source for precise layout changes. Keep the contact form and map placeholders in the Contact Us page.',
        ]);
    }

    public function update(
        Request $request,
        InformationPage $informationPage,
        PolicyContentSanitizer $sanitizer
    ): RedirectResponse {
        $validated = $request->validate([
            'nav_label' => ['required', 'string', 'max:80'],
            'meta_title' => ['required', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'string', 'max:1000'],
            'hero_eyebrow' => ['nullable', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string', 'max:1000'],
            'body_html' => ['required', 'string', 'max:1000000'],
            'is_published' => ['required', 'boolean'],
        ]);

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
}
