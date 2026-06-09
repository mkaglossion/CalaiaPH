@extends('layouts.admin')

@section('title', 'Booking Confirmation CMS')

@section('content')
@php
    $types = $types ?? [
        'staycation' => 'Staycation',
        'dayvisit' => 'Day Visit',
        'package' => 'Package',
    ];
    $hiddenTabs = ['dayvisit'];
@endphp

<div class="space-y-6" x-data="{ activeTab: 'staycation' }">
    <div class="flex flex-col gap-2">
        <h1 class="text-3xl font-semibold text-gray-800">Booking Confirmation CMS</h1>
        <p class="text-gray-600 max-w-3xl">
            Edit the static confirmation copy shown to customers after booking. Dynamic booking details such as dates, rooms, guests, and totals still come from the actual booking record.
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            <p class="font-semibold mb-2">Please fix the highlighted fields.</p>
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
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
            $content = $contents[$key] ?? [];
            $page = $content['page'] ?? [];
            $nextSteps = implode("\n", $content['next_steps']['items'] ?? []);
            $support = $content['support'] ?? [];
            $buttons = $content['buttons'] ?? [];
            $email = $content['email'] ?? [];
        @endphp

        <div x-show="activeTab === '{{ $key }}'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">{{ $label }} Confirmation</h2>
                    <p class="text-sm text-gray-500">Configure the copy used for {{ strtolower($label) }} confirmation screens and emails.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.booking-confirmation.update', $key) }}" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-4">
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Page Copy</h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Eyebrow</label>
                                <input type="text" name="contents[{{ $key }}][page_eyebrow]" value="{{ old("contents.$key.page_eyebrow", $page['eyebrow'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                                <input type="text" name="contents[{{ $key }}][page_title]" value="{{ old("contents.$key.page_title", $page['title'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="contents[{{ $key }}][page_description]" rows="3" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">{{ old("contents.$key.page_description", $page['description'] ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Next Steps</h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                                <input type="text" name="contents[{{ $key }}][next_steps_title]" value="{{ old("contents.$key.next_steps_title", $content['next_steps']['title'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Items</label>
                                <textarea name="contents[{{ $key }}][next_steps]" rows="5" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]" placeholder="One item per line">{{ old("contents.$key.next_steps", $nextSteps) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Support Info</h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Heading</label>
                                <input type="text" name="contents[{{ $key }}][support_title]" value="{{ old("contents.$key.support_title", $support['title'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="contents[{{ $key }}][support_description]" rows="3" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">{{ old("contents.$key.support_description", $support['description'] ?? '') }}</textarea>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                                    <input type="email" name="contents[{{ $key }}][support_email]" value="{{ old("contents.$key.support_email", $support['email'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Phone</label>
                                    <input type="text" name="contents[{{ $key }}][support_phone]" value="{{ old("contents.$key.support_phone", $support['phone'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Button Labels</h3>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Button</label>
                                    <input type="text" name="contents[{{ $key }}][home_button_label]" value="{{ old("contents.$key.home_button_label", $buttons['home'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Secondary Button</label>
                                    <input type="text" name="contents[{{ $key }}][print_button_label]" value="{{ old("contents.$key.print_button_label", $buttons['print'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Button</label>
                                <input type="text" name="contents[{{ $key }}][payment_button_label]" value="{{ old("contents.$key.payment_button_label", $buttons['payment'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Email Copy</h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                <input type="text" name="contents[{{ $key }}][email_subject]" value="{{ old("contents.$key.email_subject", $email['subject'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Greeting</label>
                                <input type="text" name="contents[{{ $key }}][email_greeting]" value="{{ old("contents.$key.email_greeting", $email['greeting'] ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Intro</label>
                                <textarea name="contents[{{ $key }}][email_intro]" rows="4" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">{{ old("contents.$key.email_intro", $email['intro'] ?? '') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Closing</label>
                                <textarea name="contents[{{ $key }}][email_closing]" rows="3" class="w-full rounded-lg border-gray-300 focus:border-[#8f5d54] focus:ring-[#8f5d54]">{{ old("contents.$key.email_closing", $email['closing'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-[#8f5d54] px-5 py-3 text-sm font-semibold text-white hover:bg-[#a0675a] transition-colors">
                        Save {{ $label }} Content
                    </button>
                </div>
            </form>
        </div>
    @endforeach
</div>
@endsection
