@extends('layouts.admin')

@section('title', 'Booking Confirmation CMS')

@section('content')
<style>
    .swal2-container {
        z-index: 12000 !important;
    }
</style>
@php
    $types = $types ?? [
        'staycation' => 'Staycation',
        'package' => 'Package',
        'camp-waiver' => 'Camp Waiver',
        'dayvisit' => 'Day Visit',
    ];
    $hiddenTabs = ['dayvisit'];
    $initialTab = request()->query('tab', 'staycation');
    if (!array_key_exists($initialTab, $types)) {
        $initialTab = 'staycation';
    }
@endphp

<div
    class="space-y-6"
    x-data="bookingConfirmationCms(@js($initialTab))"
>
    <div class="flex flex-col gap-2">
        <h1 class="text-3xl font-semibold text-gray-800">Booking Confirmation CMS</h1>
        <p class="text-gray-600 max-w-3xl">
            Edit page copy on the left and email copy on the right. Dynamic booking details such as dates, rooms, guests, and totals still come from the actual booking record.
        </p>
    </div>

    @if(session('success'))
        <div
            data-admin-flash
            data-admin-flash-library="swal"
            data-admin-flash-title="Success!"
            data-admin-flash-type="success"
            data-admin-flash-message="{{ e(session('success')) }}"
        ></div>
    @endif

    @if($errors->any())
        <div
            data-admin-flash
            data-admin-flash-library="swal"
            data-admin-flash-title="Error!"
            data-admin-flash-type="error"
            data-admin-flash-html="1"
            data-admin-flash-message="{{ e(implode("\n", $errors->all())) }}"
        ></div>
    @endif

    <div class="flex flex-wrap gap-2 border-b border-gray-200">
        @foreach($types as $key => $label)
            @if(in_array($key, $hiddenTabs, true))
                @continue
            @endif
            <button
                type="button"
                @click="activeTab = '{{ $key }}'"
                class="px-4 py-2 rounded-t-lg text-sm font-semibold transition"
                :class="activeTab === '{{ $key }}' ? 'bg-white text-[#8f5d54] border border-gray-200 border-b-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    @foreach($types as $key => $label)
        @if(in_array($key, $hiddenTabs, true))
            @continue
        @endif
        @php
            $contentKey = \App\Models\BookingConfirmationContent::normalizeType($key);
            $content = $contents[$contentKey] ?? [];
            $page = $content['page'] ?? [];
            $nextSteps = implode("\n", $content['next_steps']['items'] ?? []);
            $support = $content['support'] ?? [];
            $buttons = $content['buttons'] ?? [];
            $email = $content['email'] ?? [];
            $previewSrc = route('admin.booking-confirmation.preview', ['tab' => $key]);
            $fieldIdPrefix = 'booking-confirmation-' . $key;
            $controlClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-[#8f5d54] focus:ring-2 focus:ring-[#8f5d54] focus:ring-offset-1';
            $textAreaClass = $controlClass . ' min-h-[96px]';
            $buttonClass = 'inline-flex items-center justify-center rounded-lg border border-transparent bg-[#8f5d54] px-5 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:ring-offset-2';
        @endphp

        <div x-show="activeTab === '{{ $key }}'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">{{ $label }} Confirmation</h2>
                    <p class="text-sm text-gray-500">Configure the copy used for {{ strtolower($label) }} confirmation screens and emails.</p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.booking-confirmation.update', $key) }}"
                class="space-y-8"
                @input.debounce.450ms="refreshPreview($event.currentTarget)"
                @change.debounce.450ms="refreshPreview($event.currentTarget)"
            >
                @csrf
                @method('PUT')

                @if($key === 'camp-waiver')
                    @php
                        $campWaiverService = app(\App\Services\BookingConfirmationContentService::class);
                        $campWaiver = $content['camp_waiver'] ?? [];
                        $campWaiverTitle = trim((string) ($campWaiver['title'] ?? '')) !== '' ? $campWaiver['title'] : 'Camp Waiver and Release from Liability';
                        $campWaiverBody = trim((string) ($campWaiver['body'] ?? '')) !== '' ? $campWaiver['body'] : $campWaiverService->defaultCampWaiverBody();
                    @endphp

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">Camp Waiver</h3>
                        <p class="text-sm text-gray-600">
                            Edit the waiver that stays separate from the staycation confirmation copy.
                        </p>
                        <button
                            type="button"
                            class="{{ $buttonClass }} w-full sm:w-auto"
                            @click="campWaiverModal = true"
                        >
                            Open Camp Waiver Modal
                        </button>
                    </div>

                    <div
                        x-show="campWaiverModal"
                        x-cloak
                        x-transition.opacity
                        class="fixed inset-0 z-50 bg-black/50 p-4 sm:p-6"
                        @click.self="campWaiverModal = false"
                    >
                        <div class="mx-auto mt-6 w-full max-w-4xl rounded-2xl bg-white shadow-2xl border border-gray-200">
                            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-6">
                                <div>
                                    <p class="text-xs font-semibold tracking-wide text-[#8f5d54]">CAMP WAIVER</p>
                                    <h3 class="mt-1 text-base sm:text-lg font-semibold text-gray-900">Camp Waiver and Release from Liability</h3>
                                </div>
                                <button
                                    type="button"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700"
                                    @click="campWaiverModal = false"
                                    aria-label="Close camp waiver modal"
                                >
                                    <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                                </button>
                            </div>

                            <div class="max-h-[75vh] overflow-y-auto px-5 py-4 sm:px-6 sm:py-5 space-y-4">
                                <div>
                                    <label for="{{ $fieldIdPrefix }}-camp-waiver-title" class="block text-sm font-medium text-gray-700 mb-1">Waiver Title <span class="text-red-500">*</span></label>
                                    <input
                                        id="{{ $fieldIdPrefix }}-camp-waiver-title"
                                        type="text"
                                        name="contents[{{ $key }}][camp_waiver_title]"
                                        value="{{ old("contents.$key.camp_waiver_title", $campWaiverTitle) }}"
                                        class="{{ $controlClass }}"
                                        required
                                    >
                                </div>

                                <div>
                                    <label for="{{ $fieldIdPrefix }}-camp-waiver-body" class="block text-sm font-medium text-gray-700 mb-1">Waiver Body <span class="text-red-500">*</span></label>
                                    <textarea
                                        id="{{ $fieldIdPrefix }}-camp-waiver-body"
                                        name="contents[{{ $key }}][camp_waiver_body]"
                                        rows="24"
                                        class="{{ $textAreaClass }} font-mono text-sm"
                                        placeholder="Paste the full camp waiver here"
                                        required
                                    >{{ old("contents.$key.camp_waiver_body", $campWaiverBody) }}</textarea>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 border-t border-gray-100 px-5 py-4 sm:px-6">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:ring-offset-2"
                                    @click="campWaiverModal = false"
                                >
                                    Cancel
                                </button>
                                <button type="submit" class="{{ $buttonClass }}">
                                    Save Camp Waiver
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="grid gap-6 xl:grid-cols-2">
                        <div class="space-y-4">
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 space-y-5">
                                <div>
                                    <p class="text-xs font-semibold tracking-wide text-[#8f5d54] uppercase">Page Copy</p>
                                    <h3 class="mt-1 text-lg font-semibold text-gray-900">Confirmation page text</h3>
                                    <p class="text-sm text-gray-600">This controls the text shown on the actual booking confirmation page.</p>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4 shadow-sm">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Page Header</p>
                                        <p class="text-xs text-gray-500">Top text shown above the title</p>
                                    </div>
                                    <div class="grid gap-4">
                                        <div>
                                            <label for="{{ $fieldIdPrefix }}-page-eyebrow" class="block text-sm font-medium text-gray-700 mb-1">Header</label>
                                            <input id="{{ $fieldIdPrefix }}-page-eyebrow" type="text" name="contents[{{ $key }}][page_eyebrow]" value="{{ old("contents.$key.page_eyebrow", $page['eyebrow'] ?? '') }}" class="{{ $controlClass }}">
                                        </div>
                                        <div>
                                            <label for="{{ $fieldIdPrefix }}-page-title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                            <input id="{{ $fieldIdPrefix }}-page-title" type="text" name="contents[{{ $key }}][page_title]" value="{{ old("contents.$key.page_title", $page['title'] ?? '') }}" class="{{ $controlClass }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4 shadow-sm">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Header body</p>
                                        <p class="text-xs text-gray-500">Description shown under the title</p>
                                    </div>
                                    <div>
                                        <label for="{{ $fieldIdPrefix }}-page-description" class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                                        <textarea id="{{ $fieldIdPrefix }}-page-description" name="contents[{{ $key }}][page_description]" rows="3" class="{{ $textAreaClass }}">{{ old("contents.$key.page_description", $page['description'] ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4 shadow-sm">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Subheader</p>
                                        <p class="text-xs text-gray-500">Whats next title</p>
                                    </div>
                                    <div>
                                        <label for="{{ $fieldIdPrefix }}-next-steps-title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                        <input id="{{ $fieldIdPrefix }}-next-steps-title" type="text" name="contents[{{ $key }}][next_steps_title]" value="{{ old("contents.$key.next_steps_title", $content['next_steps']['title'] ?? '') }}" class="{{ $controlClass }}">
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4 shadow-sm">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Subheader body</p>
                                        <p class="text-xs text-gray-500">One item per line</p>
                                    </div>
                                    <div>
                                        <label for="{{ $fieldIdPrefix }}-next-steps-body" class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                                        <textarea id="{{ $fieldIdPrefix }}-next-steps-body" name="contents[{{ $key }}][next_steps]" rows="5" class="{{ $textAreaClass }}" placeholder="One item per line">{{ old("contents.$key.next_steps", $nextSteps) }}</textarea>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4 shadow-sm">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Footer</p>
                                        <p class="text-xs text-gray-500">Footer title and body</p>
                                    </div>
                                    <div class="grid gap-4">
                                        <div>
                                            <label for="{{ $fieldIdPrefix }}-support-title" class="block text-sm font-medium text-gray-700 mb-1">Footer title</label>
                                            <input id="{{ $fieldIdPrefix }}-support-title" type="text" name="contents[{{ $key }}][support_title]" value="{{ old("contents.$key.support_title", $support['title'] ?? '') }}" class="{{ $controlClass }}">
                                        </div>
                                        <div>
                                            <label for="{{ $fieldIdPrefix }}-support-description" class="block text-sm font-medium text-gray-700 mb-1">Footer body</label>
                                            <textarea id="{{ $fieldIdPrefix }}-support-description" name="contents[{{ $key }}][support_description]" rows="3" class="{{ $textAreaClass }}">{{ old("contents.$key.support_description", $support['description'] ?? '') }}</textarea>
                                        </div>
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <label for="{{ $fieldIdPrefix }}-support-email" class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                                                <input id="{{ $fieldIdPrefix }}-support-email" type="email" name="contents[{{ $key }}][support_email]" value="{{ old("contents.$key.support_email", $support['email'] ?? '') }}" class="{{ $controlClass }}">
                                            </div>
                                            <div>
                                                <label for="{{ $fieldIdPrefix }}-support-phone" class="block text-sm font-medium text-gray-700 mb-1">Support Phone</label>
                                                <input id="{{ $fieldIdPrefix }}-support-phone" type="text" name="contents[{{ $key }}][support_phone]" value="{{ old("contents.$key.support_phone", $support['phone'] ?? '') }}" class="{{ $controlClass }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4 shadow-sm">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Footer button labels</p>
                                        <p class="text-xs text-gray-500">These are the buttons shown at the bottom of the confirmation page.</p>
                                    </div>
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="{{ $fieldIdPrefix }}-home-button" class="block text-sm font-medium text-gray-700 mb-1">Primary button</label>
                                            <input id="{{ $fieldIdPrefix }}-home-button" type="text" name="contents[{{ $key }}][home_button_label]" value="{{ old("contents.$key.home_button_label", $buttons['home'] ?? '') }}" placeholder="{{ $buttons['home'] ?? 'Return to Home' }}" class="{{ $controlClass }}">
                                        </div>
                                        <div>
                                            <label for="{{ $fieldIdPrefix }}-print-button" class="block text-sm font-medium text-gray-700 mb-1">Secondary button</label>
                                            <input id="{{ $fieldIdPrefix }}-print-button" type="text" name="contents[{{ $key }}][print_button_label]" value="{{ old("contents.$key.print_button_label", $buttons['print'] ?? '') }}" placeholder="{{ $buttons['print'] ?? 'Print Confirmation' }}" class="{{ $controlClass }}">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="{{ $fieldIdPrefix }}-payment-button" class="block text-sm font-medium text-gray-700 mb-1">Payment button</label>
                                        <input id="{{ $fieldIdPrefix }}-payment-button" type="text" name="contents[{{ $key }}][payment_button_label]" value="{{ old("contents.$key.payment_button_label", $buttons['payment'] ?? '') }}" placeholder="{{ $buttons['payment'] ?? 'Proceed to Payment' }}" class="{{ $controlClass }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 space-y-5">
                                <div>
                                    <p class="text-xs font-semibold tracking-wide text-[#8f5d54] uppercase">Email Copy</p>
                                    <h3 class="mt-1 text-lg font-semibold text-gray-900">Confirmation email text</h3>
                                    <p class="text-sm text-gray-600">This controls the email sent to the customer after booking.</p>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4 shadow-sm">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Subject</p>
                                        <p class="text-xs text-gray-500">Email subject line</p>
                                    </div>
                                    <div>
                                        <label for="{{ $fieldIdPrefix }}-email-subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                        <input id="{{ $fieldIdPrefix }}-email-subject" type="text" name="contents[{{ $key }}][email_subject]" value="{{ old("contents.$key.email_subject", $email['subject'] ?? '') }}" class="{{ $controlClass }}">
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4 shadow-sm">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Body 1</p>
                                        <p class="text-xs text-gray-500">Greeting or first paragraph</p>
                                    </div>
                                    <div>
                                        <label for="{{ $fieldIdPrefix }}-email-greeting" class="block text-sm font-medium text-gray-700 mb-1">Body 1</label>
                                        <textarea id="{{ $fieldIdPrefix }}-email-greeting" name="contents[{{ $key }}][email_greeting]" rows="4" class="{{ $textAreaClass }}">{{ old("contents.$key.email_greeting", $email['greeting'] ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4 shadow-sm">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Body 2</p>
                                        <p class="text-xs text-gray-500">Main email body</p>
                                    </div>
                                    <div>
                                        <label for="{{ $fieldIdPrefix }}-email-intro" class="block text-sm font-medium text-gray-700 mb-1">Body 2</label>
                                        <textarea id="{{ $fieldIdPrefix }}-email-intro" name="contents[{{ $key }}][email_intro]" rows="6" class="{{ $textAreaClass }}">{{ old("contents.$key.email_intro", $email['intro'] ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4 shadow-sm">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Signature</p>
                                        <p class="text-xs text-gray-500">Closing line at the bottom of the email</p>
                                    </div>
                                    <div>
                                        <label for="{{ $fieldIdPrefix }}-email-closing" class="block text-sm font-medium text-gray-700 mb-1">Signature</label>
                                        <textarea id="{{ $fieldIdPrefix }}-email-closing" name="contents[{{ $key }}][email_closing]" rows="4" class="{{ $textAreaClass }}">{{ old("contents.$key.email_closing", $email['closing'] ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:ring-offset-2"
                            @click="openPreview(@js($previewSrc), @js($label . ' Confirmation Preview'), $event.currentTarget.closest('form'))"
                        >
                            Preview live content
                        </button>
                        <button type="submit" class="{{ $buttonClass }}">
                            Save {{ $label }} Content
                        </button>
                    </div>
                @endif
            </form>
        </div>
    @endforeach

    <div
        x-cloak
        x-show="previewOpen"
        class="fixed inset-0 z-50 bg-black/60 p-4 sm:p-6"
        @click.self="closePreview()"
    >
        <div class="mx-auto flex h-full w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-6">
                <div>
                    <p class="text-xs font-semibold tracking-wide text-[#8f5d54] uppercase">Actual Preview</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900" x-text="previewTitle"></h3>
                    <p class="text-sm text-gray-600">This shows the actual customer view and updates as you type.</p>
                </div>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700"
                    @click="closePreview()"
                    aria-label="Close preview"
                >
                    <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                </button>
            </div>

            <div class="flex-1 bg-gray-50 p-3 sm:p-4">
                <iframe
                    name="bookingConfirmationPreviewFrame"
                    x-ref="previewFrame"
                    title="Booking confirmation preview"
                    class="h-full w-full rounded-2xl border border-gray-200 bg-white"
                ></iframe>
            </div>
        </div>
    </div>
</div>

@endsection
