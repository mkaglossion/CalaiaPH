@extends('layouts.admin')

@section('title', 'Help & Documentation')

@section('content')
@php
    $helpCategories = [
        [
            'id' => 'getting-started',
            'title' => 'Getting Started',
            'description' => 'Learn the basics of using the admin panel.',
            'icon' => 'rocket',
            'iconBg' => 'bg-blue-100',
            'iconColor' => 'text-blue-600',
            'articles' => [
                [
                    'title' => 'Welcome to Calaia PH Admin Panel',
                    'body' => 'The admin panel is your central hub for managing bookings, content, pricing, alerts, and administrative settings for Calaia PH.'
                ],
                [
                    'title' => 'Logging In',
                    'body' => 'Use your assigned email or username and password to sign in. Your account role determines which modules and actions you can access.'
                ],
                [
                    'title' => 'Your Profile',
                    'list' => [
                        'Update your personal information in Settings → Profile.',
                        'Change your password regularly for security.',
                        'Review your activity and notification preferences when available.',
                    ]
                ],
                [
                    'title' => 'Dashboard Overview',
                    'body' => 'The dashboard gives you a quick overview of business activity, alerts, reservations, and important updates that may require action.'
                ],
            ],
        ],
        [
            'id' => 'content-management',
            'title' => 'Content Management',
            'description' => 'Manage website pages, media, and content items.',
            'icon' => 'file-text',
            'iconBg' => 'bg-green-100',
            'iconColor' => 'text-green-600',
            'articles' => [
                [
                    'title' => 'Updating Website Content',
                    'body' => 'Open the relevant module such as Home, Day Tour, Where to Stay, Food & Beverage, Our Farm, or Shop to update page information and displayed content.'
                ],
                [
                    'title' => 'Uploading Images',
                    'list' => [
                        'Use high-quality images for cards, banners, and galleries.',
                        'Recommended size: 1200×800px or larger.',
                        'Keep files optimized for faster loading.',
                        'Supported formats commonly include JPG, PNG, and WebP.',
                    ]
                ],
                [
                    'title' => 'Editing Content',
                    'body' => 'Use the edit actions available in each module. Review labels, descriptions, prices, and linked images before saving changes.'
                ],
                [
                    'title' => 'Deleting Items',
                    'body' => 'Delete only when necessary. Removed content may not be recoverable depending on the module and system behavior.'
                ],
            ],
        ],
        [
            'id' => 'bookings',
            'title' => 'Bookings',
            'description' => 'Handle reservations and booking operations.',
            'icon' => 'calendar-days',
            'iconBg' => 'bg-purple-100',
            'iconColor' => 'text-purple-600',
            'articles' => [
                [
                    'title' => 'Viewing Bookings',
                    'body' => 'Go to the Bookings module to view all customer reservations. Use filters and search tools to narrow results by date, status, or guest.'
                ],
                [
                    'title' => 'Booking Status Guide',
                    'list' => [
                        'Pending — awaiting review or payment confirmation.',
                        'Confirmed — reservation has been approved or paid.',
                        'Cancelled — booking was cancelled.',
                        'Completed — stay or visit has already ended.',
                    ]
                ],
                [
                    'title' => 'Inventory Monitoring',
                    'body' => 'Track day tour and staycation availability through the inventory screens to avoid overbooking and scheduling conflicts.'
                ],
                [
                    'title' => 'Notifications',
                    'body' => 'System alerts or email notifications may be used for new or updated bookings depending on your current configuration.'
                ],
            ],
        ],
        [
            'id' => 'pricing-settings',
            'title' => 'Pricing & Settings',
            'description' => 'Configure rates, policies, and operational settings.',
            'icon' => 'badge-dollar-sign',
            'iconBg' => 'bg-yellow-100',
            'iconColor' => 'text-yellow-600',
            'articles' => [
                [
                    'title' => 'Setting Prices',
                    'body' => 'Manage rates for accommodations, activities, products, and services through the pricing or rate matrix configuration sections.'
                ],
                [
                    'title' => 'Holiday and Special Rates',
                    'body' => 'Apply seasonal or holiday pricing where supported. Review schedules carefully before publishing changes.'
                ],
                [
                    'title' => 'Business Rules',
                    'body' => 'System settings may include currency, business hours, booking rules, or configurable operational values based on your implementation.'
                ],
                [
                    'title' => 'Additional Fees',
                    'body' => 'Use the appropriate configuration pages to maintain surcharges, add-ons, or other pricing adjustments.'
                ],
            ],
        ],
        [
            'id' => 'users-permissions',
            'title' => 'Users & Permissions',
            'description' => 'Manage user access and administrative responsibilities.',
            'icon' => 'users',
            'iconBg' => 'bg-red-100',
            'iconColor' => 'text-red-600',
            'articles' => [
                [
                    'title' => 'Managing Admin Users',
                    'body' => 'Authorized administrators can review and maintain admin access, depending on role restrictions and system permissions.'
                ],
                [
                    'title' => 'Permission Levels',
                    'body' => 'Different users may have different access to pages such as bookings, pricing, alerts, and settings. Access is based on role or assigned permissions.'
                ],
                [
                    'title' => 'Security Best Practices',
                    'list' => [
                        'Do not share your account credentials.',
                        'Use strong passwords and update them regularly.',
                        'Log out when using shared or public devices.',
                    ]
                ],
            ],
        ],
        [
            'id' => 'system-admin',
            'title' => 'System Administration',
            'description' => 'Review alerts, logs, and system-wide settings.',
            'icon' => 'settings',
            'iconBg' => 'bg-indigo-100',
            'iconColor' => 'text-indigo-600',
            'articles' => [
                [
                    'title' => 'System Settings',
                    'body' => 'Use System Settings to manage app-wide preferences such as naming, operational defaults, and administrative configuration.'
                ],
                [
                    'title' => 'Maintenance and Monitoring',
                    'body' => 'Review alerts regularly and verify that critical issues are resolved promptly to maintain smooth operations.'
                ],
                [
                    'title' => 'Activity Logs',
                    'body' => 'Use logs to track changes, review actions, and support internal audit or troubleshooting workflows.'
                ],
                [
                    'title' => 'Backups and Recovery',
                    'body' => 'Maintain a regular backup practice before major system or content updates whenever backup tools are available.'
                ],
            ],
        ],
    ];

    $faqs = [
        [
            'q' => 'How do I reset my password?',
            'a' => 'Go to Settings → Profile and use the password update option. Enter your current password, then your new password.'
        ],
        [
            'q' => 'Can I undo a deleted item?',
            'a' => 'In many cases, deleted items are not automatically recoverable. Double-check before deleting important records or content.'
        ],
        [
            'q' => 'How often should I create backups?',
            'a' => 'Create backups regularly, especially before large content edits, pricing changes, or system updates.'
        ],
        [
            'q' => 'What should I do if I see a critical alert?',
            'a' => 'Open the Alerts section immediately, review the message, and take the necessary corrective action or escalate the issue if needed.'
        ],
        [
            'q' => 'How do I search for a booking?',
            'a' => 'Use the Bookings page search and filters to find reservations by guest details, booking reference, date, or status.'
        ],
    ];
@endphp

<div
    x-data="helpCenter({
        categories: {{ \Illuminate\Support\Js::from($helpCategories) }}
    })"
    x-init="init()"
    class="space-y-6"
>
    {{-- Page Header --}}
    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="bg-linear-to-r from-[#8f5d54] via-[#9b6b62] to-[#b1867d] px-6 py-8 md:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl text-white">
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-medium uppercase tracking-wide">
                        <i data-lucide="life-buoy" class="h-4 w-4"></i>
                        Help Center
                    </div>

                    <h1 class="mt-4 text-3xl font-bold tracking-tight md:text-4xl">
                        Help & Documentation
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm text-white/90 md:text-base">
                        Find answers, explore admin workflows, and quickly navigate the Calaia PH admin panel with a cleaner, searchable guide.
                    </p>
                </div>

                <div class="flex gap-3">
                    <div class="rounded-xl bg-white/10 px-4 py-3 text-white backdrop-blur-sm">
                        <p class="text-xs uppercase tracking-wide text-white/70">Topics</p>
                        <p class="mt-1 text-2xl font-semibold">{{ count($helpCategories) }}</p>
                    </div>
                    <div class="rounded-xl bg-white/10 px-4 py-3 text-white backdrop-blur-sm">
                        <p class="text-xs uppercase tracking-wide text-white/70">FAQs</p>
                        <p class="mt-1 text-2xl font-semibold">{{ count($faqs) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-white/10 bg-white px-6 py-5 md:px-8">
            <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                <div class="relative">
                    <i data-lucide="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"></i>
                    <input
                        id="help-search"
                        name="help_search"
                        x-model.debounce.250ms="query"
                        type="text"
                        placeholder="Search topics, keywords, or FAQs..."
                        class="w-full rounded-xl border border-gray-300 bg-white py-3 pl-12 pr-4 text-sm text-gray-900 placeholder:text-gray-400 focus:border-[#8f5d54] focus:outline-none focus:ring-4 focus:ring-[#8f5d54]/10"
                    >
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        @click="expandAll()"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        <i data-lucide="plus-square" class="h-4 w-4"></i>
                        Expand all
                    </button>

                    <button
                        type="button"
                        @click="collapseAll()"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        <i data-lucide="minus-square" class="h-4 w-4"></i>
                        Collapse all
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Quick Navigation --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        <template x-for="category in filteredCategories" :key="category.id">
            <button
                type="button"
                @click="openSection(category.id)"
                class="group rounded-2xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-md"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                        :class="category.iconBg"
                    >
                        <i :data-lucide="category.icon" class="h-5 w-5" :class="category.iconColor"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <h2 class="text-base font-semibold text-gray-900" x-text="category.title"></h2>
                            <i data-lucide="arrow-up-right" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400 transition group-hover:text-gray-700"></i>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-gray-600" x-text="category.description"></p>
                    </div>
                </div>
            </button>
        </template>
    </section>

    {{-- No Search Results --}}
    <div
        x-show="filteredCategories.length === 0"
        x-transition
        class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center shadow-sm"
    >
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
            <i data-lucide="search-x" class="h-6 w-6 text-gray-500"></i>
        </div>
        <h3 class="mt-4 text-lg font-semibold text-gray-900">No matching help topics</h3>
        <p class="mt-2 text-sm text-gray-500">
            Try a different keyword or browse the categories below.
        </p>
    </div>

    {{-- Detailed Help Sections --}}
    <section class="space-y-4">
        <template x-for="category in filteredCategories" :key="`section-${category.id}`">
            <article
                :id="category.id"
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
            >
                <button
                    type="button"
                    class="flex w-full items-start justify-between gap-4 px-6 py-5 text-left md:px-8"
                    @click="toggle(category.id)"
                >
                    <div class="flex min-w-0 items-start gap-4">
                        <div
                            class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                            :class="category.iconBg"
                        >
                            <i :data-lucide="category.icon" class="h-5 w-5" :class="category.iconColor"></i>
                        </div>

                        <div class="min-w-0">
                            <h3 class="text-xl font-semibold text-gray-900" x-text="category.title"></h3>
                            <p class="mt-1 text-sm text-gray-500" x-text="category.description"></p>
                        </div>
                    </div>

                    <i
                        data-lucide="chevron-down"
                        class="mt-1 h-5 w-5 shrink-0 text-gray-400 transition-transform duration-200"
                        :class="{ 'rotate-180': isOpen(category.id) }"
                    ></i>
                </button>

                <div
                    x-show="isOpen(category.id)"
                    x-collapse
                    class="border-t border-gray-100 px-6 py-6 md:px-8"
                >
                    <div class="grid gap-4">
                        <template x-for="article in category.articles" :key="article.title">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                                <h4 class="text-base font-semibold text-gray-900" x-text="article.title"></h4>

                                <template x-if="article.body">
                                    <p class="mt-2 text-sm leading-7 text-gray-700" x-text="article.body"></p>
                                </template>

                                <template x-if="article.list">
                                    <ul class="mt-3 space-y-2">
                                        <template x-for="item in article.list" :key="item">
                                            <li class="flex items-start gap-3 text-sm leading-6 text-gray-700">
                                                <i data-lucide="check-circle-2" class="mt-0.5 h-4 w-4 shrink-0 text-green-600"></i>
                                                <span x-text="item"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </article>
        </template>
    </section>

    {{-- FAQ --}}
    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-5 md:px-8">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-100">
                    <i data-lucide="messages-square" class="h-5 w-5 text-sky-600"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">Frequently Asked Questions</h3>
                    <p class="mt-1 text-sm text-gray-500">Quick answers to the most common admin concerns.</p>
                </div>
            </div>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($faqs as $faq)
                <div class="px-6 py-5 md:px-8">
                    <h4 class="text-sm font-semibold text-gray-900">{{ $faq['q'] }}</h4>
                    <p class="mt-2 text-sm leading-7 text-gray-600">{{ $faq['a'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    function helpCenter(config) {
        return {
            categories: config.categories || [],
            query: '',
            openSections: [],

            init() {
                this.openSections = this.categories.length ? [this.categories[0].id] : [];
                this.refreshIcons();
                this.$watch('query', () => {
                    this.$nextTick(() => this.refreshIcons());
                });
            },

            get filteredCategories() {
                if (!this.query.trim()) return this.categories;

                const q = this.query.toLowerCase();

                return this.categories.filter(category => {
                    const inTitle = category.title.toLowerCase().includes(q);
                    const inDescription = category.description.toLowerCase().includes(q);
                    const inArticles = category.articles.some(article => {
                        const articleTitle = (article.title || '').toLowerCase();
                        const articleBody = (article.body || '').toLowerCase();
                        const articleList = Array.isArray(article.list)
                            ? article.list.join(' ').toLowerCase()
                            : '';

                        return articleTitle.includes(q) || articleBody.includes(q) || articleList.includes(q);
                    });

                    return inTitle || inDescription || inArticles;
                });
            },

            isOpen(id) {
                return this.openSections.includes(id);
            },

            toggle(id) {
                if (this.isOpen(id)) {
                    this.openSections = this.openSections.filter(sectionId => sectionId !== id);
                } else {
                    this.openSections.push(id);
                }

                this.$nextTick(() => this.refreshIcons());
            },

            openSection(id) {
                if (!this.isOpen(id)) {
                    this.openSections.push(id);
                }

                this.$nextTick(() => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    this.refreshIcons();
                });
            },

            expandAll() {
                this.openSections = this.filteredCategories.map(category => category.id);
                this.$nextTick(() => this.refreshIcons());
            },

            collapseAll() {
                this.openSections = [];
                this.$nextTick(() => this.refreshIcons());
            },

            refreshIcons() {
                if (window.initLucideIcons) {
                    setTimeout(() => window.initLucideIcons(), 0);
                } else if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    setTimeout(() => window.lucide.createIcons(), 0);
                }
            }
        }
    }
</script>
@endpush