@php
    $cmsTitle = $cmsTitle ?? 'Policies & Terms CMS';
    $cmsDescription = $cmsDescription ?? 'Manage navigation labels, SEO, hero copy, publication status, and the complete content for every public policy page.';
    $cmsNavLabel = $cmsNavLabel ?? 'Policy pages';
    $configNamespace = $configNamespace ?? 'policy_pages';
    $adminIndexRoute = $adminIndexRoute ?? 'admin.policies.index';
    $adminUpdateRoute = $adminUpdateRoute ?? 'admin.policies.update';
    $supportsLastUpdated = $supportsLastUpdated ?? true;
    $bodyHelpText = $bodyHelpText ?? 'Edit visually or switch to HTML source for precise layout changes.';
    $editableBodyHtml = $editableBodyHtml ?? $policyPage->body_html;
    $publicRouteName = config("{$configNamespace}.pages.{$policyPage->slug}.route");
@endphp

@extends('layouts.admin')

@section('title', $cmsTitle)

@section('content')
<style>
    .policy-admin-editor {
        min-height: 34rem;
        max-height: 46rem;
        overflow: auto;
        background: #fff;
    }

    .policy-admin-editor:focus {
        outline: 2px solid #8f5d54;
        outline-offset: 2px;
    }
</style>

<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900">{{ $cmsTitle }}</h1>
            <p class="mt-2 max-w-3xl text-gray-600">
                {{ $cmsDescription }}
            </p>
        </div>

        <a
            href="{{ route($publicRouteName) }}"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700"
        >
            <i data-lucide="external-link" class="h-4 w-4"></i>
            View live page
        </a>
    </div>

    @if(session('success'))
        <div
            data-admin-flash
            data-admin-flash-library="swal"
            data-admin-flash-title="Saved!"
            data-admin-flash-type="success"
            data-admin-flash-message="{{ e(session('success')) }}"
        ></div>
    @endif

    @if($errors->any())
        <div
            data-admin-flash
            data-admin-flash-library="swal"
            data-admin-flash-title="Please check the form"
            data-admin-flash-type="error"
            data-admin-flash-html="1"
            data-admin-flash-message="{{ e(implode("\n", $errors->all())) }}"
        ></div>
    @endif

    <nav class="flex gap-2 overflow-x-auto border-b border-gray-200 pb-px" aria-label="{{ $cmsNavLabel }}">
        @foreach($pages as $page)
            <a
                href="{{ route($adminIndexRoute, ['page' => $page->slug]) }}"
                class="whitespace-nowrap rounded-t-lg border px-4 py-3 text-sm font-semibold transition {{ $page->is($policyPage) ? 'border-gray-200 border-b-white bg-white text-[#8f5d54]' : 'border-transparent bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
            >
                {{ $page->nav_label }}
                @unless($page->is_published)
                    <span class="ml-1 rounded-full bg-gray-200 px-2 py-0.5 text-xs text-gray-600">Draft</span>
                @endunless
            </a>
        @endforeach
    </nav>

    <form
        method="POST"
        action="{{ route($adminUpdateRoute, $policyPage->slug) }}"
        class="space-y-6"
        onsubmit="syncPolicyEditorBeforeSubmit()"
    >
        @csrf
        @method('PUT')

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5">
                    <h2 class="text-xl font-semibold text-gray-900">Page and navigation</h2>
                    <p class="text-sm text-gray-500">Controls the footer link and public page header.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="space-y-2 sm:col-span-2">
                        <span class="text-sm font-semibold text-gray-700">Navigation label</span>
                        <input type="text" name="nav_label" value="{{ old('nav_label', $policyPage->nav_label) }}" maxlength="80" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">
                    </label>

                    <label class="space-y-2">
                        <span class="text-sm font-semibold text-gray-700">Hero eyebrow</span>
                        <input type="text" name="hero_eyebrow" value="{{ old('hero_eyebrow', $policyPage->hero_eyebrow) }}" maxlength="120" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20" placeholder="Optional small heading">
                    </label>

                    @if($supportsLastUpdated)
                        <label class="space-y-2">
                            <span class="text-sm font-semibold text-gray-700">Last updated</span>
                            <input type="date" name="last_updated" value="{{ old('last_updated', $policyPage->last_updated?->format('Y-m-d')) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">
                        </label>
                    @endif

                    <label class="space-y-2 sm:col-span-2">
                        <span class="text-sm font-semibold text-gray-700">Hero title</span>
                        <input type="text" name="hero_title" value="{{ old('hero_title', $policyPage->hero_title) }}" maxlength="255" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">
                    </label>

                    <label class="space-y-2 sm:col-span-2">
                        <span class="text-sm font-semibold text-gray-700">Hero description</span>
                        <textarea name="hero_description" rows="4" maxlength="1000" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">{{ old('hero_description', $policyPage->hero_description) }}</textarea>
                        <span class="block text-xs text-gray-500">Use a new line to split the description into two lines.</span>
                    </label>

                    <div class="sm:col-span-2 rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <input type="hidden" name="is_published" value="0">
                        <label class="flex items-start gap-3">
                            <input type="checkbox" name="is_published" value="1" @checked((bool) old('is_published', $policyPage->is_published)) class="mt-1 h-4 w-4 rounded border-gray-300 text-[#8f5d54] focus:ring-[#8f5d54]">
                            <span>
                                <span class="block text-sm font-semibold text-gray-900">Published</span>
                                <span class="block text-xs text-gray-500">Unpublished pages return a 404 and are removed from the footer.</span>
                            </span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5">
                    <h2 class="text-xl font-semibold text-gray-900">Search and sharing</h2>
                    <p class="text-sm text-gray-500">Metadata used by search engines and link previews.</p>
                </div>

                <div class="space-y-5">
                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-gray-700">Meta title</span>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $policyPage->meta_title) }}" maxlength="255" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-gray-700">Meta description</span>
                        <textarea name="meta_description" rows="4" maxlength="1000" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">{{ old('meta_description', $policyPage->meta_description) }}</textarea>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-gray-700">Meta keywords</span>
                        <textarea name="meta_keywords" rows="3" maxlength="1000" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54]/20">{{ old('meta_keywords', $policyPage->meta_keywords) }}</textarea>
                    </label>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                        <span class="font-semibold text-gray-900">Public URL:</span>
                        <span class="break-all">{{ route($publicRouteName) }}</span>
                    </div>
                </div>
            </section>
        </div>

        @php($bodyHtml = old('body_html', $editableBodyHtml))
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 p-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Full page content</h2>
                    <p class="text-sm text-gray-500">
                        {{ $bodyHelpText }}
                        @if($supportsLastUpdated)
                            Use <code class="rounded bg-gray-200 px-1">@{{last_updated}}</code> wherever the selected date should appear.
                        @elseif($policyPage->slug === 'contact')
                            Keep <code class="rounded bg-gray-200 px-1">@{{contact_form}}</code> and <code class="rounded bg-gray-200 px-1">@{{contact_map}}</code> so the working form and map remain on the page.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button" id="visualModeButton" onclick="showPolicyVisualMode()" class="rounded-lg bg-[#8f5d54] px-3 py-2 text-sm font-semibold text-white">Visual</button>
                    <button type="button" id="sourceModeButton" onclick="showPolicySourceMode()" class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300">HTML source</button>
                    <button type="button" onclick="formatPolicyContent('bold')" class="rounded-lg bg-white px-3 py-2 text-sm font-bold text-gray-700 ring-1 ring-gray-300" title="Bold">B</button>
                    <button type="button" onclick="formatPolicyContent('italic')" class="rounded-lg bg-white px-3 py-2 text-sm italic text-gray-700 ring-1 ring-gray-300" title="Italic">I</button>
                    <button type="button" onclick="formatPolicyContent('insertUnorderedList')" class="rounded-lg bg-white px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-300" title="Bulleted list">List</button>
                    <button type="button" onclick="addPolicyLink()" class="rounded-lg bg-white px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-300" title="Add link">Link</button>
                </div>
            </div>

            <div id="policyVisualEditor" class="policy-admin-editor" contenteditable="true" role="textbox" aria-multiline="true">{!! $bodyHtml !!}</div>
            <textarea id="policySourceEditor" name="body_html" class="hidden min-h-[46rem] w-full border-0 bg-gray-950 p-5 font-mono text-sm leading-relaxed text-gray-100 focus:ring-2 focus:ring-[#8f5d54]">{{ $bodyHtml }}</textarea>
        </section>

        <div class="sticky bottom-4 z-20 flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white/95 p-4 shadow-xl backdrop-blur sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-500">
                @if($policyPage->updatedBy)
                    Last saved by {{ $policyPage->updatedBy->first_name }} on {{ $policyPage->updated_at->format('M j, Y g:i A') }}.
                @else
                    This page is using its initial CMS content.
                @endif
            </p>

            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#8f5d54] px-6 py-3 text-sm font-semibold text-white shadow transition hover:bg-[#7a4d46] focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:ring-offset-2">
                <i data-lucide="save" class="h-4 w-4"></i>
                Save {{ $policyPage->nav_label }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const policyVisualEditor = document.getElementById('policyVisualEditor');
    const policySourceEditor = document.getElementById('policySourceEditor');
    const visualModeButton = document.getElementById('visualModeButton');
    const sourceModeButton = document.getElementById('sourceModeButton');

    function showPolicyVisualMode() {
        policyVisualEditor.innerHTML = policySourceEditor.value;
        policyVisualEditor.classList.remove('hidden');
        policySourceEditor.classList.add('hidden');
        visualModeButton.className = 'rounded-lg bg-[#8f5d54] px-3 py-2 text-sm font-semibold text-white';
        sourceModeButton.className = 'rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300';
    }

    function showPolicySourceMode() {
        policySourceEditor.value = policyVisualEditor.innerHTML;
        policyVisualEditor.classList.add('hidden');
        policySourceEditor.classList.remove('hidden');
        sourceModeButton.className = 'rounded-lg bg-[#8f5d54] px-3 py-2 text-sm font-semibold text-white';
        visualModeButton.className = 'rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300';
    }

    function formatPolicyContent(command) {
        if (policyVisualEditor.classList.contains('hidden')) {
            showPolicyVisualMode();
        }

        policyVisualEditor.focus();
        document.execCommand(command, false, null);
    }

    function addPolicyLink() {
        const url = window.prompt('Enter a safe URL, email link, or phone link:');

        if (!url) {
            return;
        }

        if (policyVisualEditor.classList.contains('hidden')) {
            showPolicyVisualMode();
        }

        policyVisualEditor.focus();
        document.execCommand('createLink', false, url);
    }

    function syncPolicyEditorBeforeSubmit() {
        if (!policyVisualEditor.classList.contains('hidden')) {
            policySourceEditor.value = policyVisualEditor.innerHTML;
        }
    }
</script>
@endpush
