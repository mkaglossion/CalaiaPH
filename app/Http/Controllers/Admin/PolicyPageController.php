<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PolicyPage;
use App\Services\PolicyContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PolicyPageController extends Controller
{
    public function index(Request $request): View
    {
        $pages = PolicyPage::ensureDefaults()
            ->filter(static fn (PolicyPage $page): bool => PolicyPage::isVisibleInNavigation($page->slug))
            ->values();
        $requestedSlug = (string) $request->query('page', $pages->first()?->slug);
        $policyPage = $pages->firstWhere('slug', $requestedSlug) ?? $pages->firstOrFail();

        return view('admin.policies.index', compact('pages', 'policyPage'));
    }

    public function update(
        Request $request,
        PolicyPage $policyPage,
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
            'last_updated' => ['required', 'date'],
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
        $policyPage->update($validated);

        return redirect()
            ->route('admin.policies.index', ['page' => $policyPage->slug])
            ->with('success', $policyPage->nav_label.' was updated successfully.');
    }
}
