{{-- resources/views/pages/our-services.blade.php --}}
<x-page-layout
    :title="$informationPage->meta_title"
    :description="$informationPage->meta_description"
    :keywords="$informationPage->meta_keywords"
    :active-header="$informationPage->slug"
>
    {{-- Best practice:
        - Tailwind-only styling (no custom CSS needed)
        - Accessible focus states
        - Reduced-motion friendly (Tailwind motion-reduce)
        - Clean spacing + consistent container widths
    --}}

    <div class="min-h-screen bg-white">
        {{-- HERO --}}
        @if($informationPage->shouldShowHero())
        <section class="relative h-screen w-full overflow-hidden bg-linear-to-br from-amber-900 via-amber-800 to-amber-950">
            {{-- Content --}}
            <div class="relative z-10 flex h-full flex-col items-center justify-center px-4 text-center">
                <div class="max-w-4xl space-y-8">
                    @if(filled($informationPage->hero_eyebrow))
                        <p class="text-sm font-bold uppercase tracking-[0.3em] text-amber-200">{{ $informationPage->hero_eyebrow }}</p>
                    @endif
                    <h1 class="text-5xl font-black text-white drop-shadow-lg sm:text-6xl md:text-7xl">
                        <span class="text-amber-300">{{ $informationPage->hero_title }}</span>
                    </h1>

                    @if(filled($informationPage->hero_description))
                        @php
                            $heroLines = preg_split('/\R/u', trim($informationPage->hero_description)) ?: [];
                        @endphp
                        <p class="text-xl font-bold leading-relaxed text-white drop-shadow-md md:text-2xl">
                            {{ array_shift($heroLines) }}
                            @foreach($heroLines as $heroLine)
                                <span class="block font-black text-amber-300">{{ $heroLine }}</span>
                            @endforeach
                        </p>
                    @endif
                </div>
            </div>
        </section>
        @endif

        <!-- INFORMATION_PAGE_BODY_START -->
        @if($informationPage->hasStoredBody())
            {!! $informationPage->renderedBodyHtml() !!}
        @else
        {{-- CONTENT --}}
        <section class="bg-white py-12 sm:py-16 pb-0">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

                {{-- ACCOMMODATION SERVICES --}}
                <article id="accommodation" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Stay</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Accommodation Services
                        </h2>
                    </div>

                    <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
                        {{-- Comfortable Stays --}}
                        <div class="rounded-2xl bg-white shadow-lg ring-2 ring-amber-200 transition hover:shadow-xl hover:ring-amber-300 p-8">
                            <div class="mb-6 flex justify-center">
                                <div class="inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-amber-600 text-white">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4m-9-2l-2-3m0 0L9 7m0 0l7-4" /></svg>
                                </div>
                            </div>
                            <h3 class="mb-4 text-lg font-bold text-gray-900 text-center">Comfortable Stays</h3>
                            <p class="leading-relaxed text-gray-800">Experience authentic farm life in our cozy accommodations. Each room is designed to provide comfort while maintaining the rustic charm of farm living.</p>
                        </div>

                        {{-- Farm-to-Table Dining --}}
                        <div class="rounded-2xl bg-white shadow-lg ring-2 ring-amber-200 transition hover:shadow-xl hover:ring-amber-300 p-8">
                            <div class="mb-6 flex justify-center">
                                <div class="inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-amber-600 text-white">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                                </div>
                            </div>
                            <h3 class="mb-4 text-lg font-bold text-gray-900 text-center">Farm-to-Table Dining</h3>
                            <p class="leading-relaxed text-gray-800">Enjoy meals prepared with fresh ingredients harvested from our own farm. Our chefs create delicious dishes that showcase the best of local produce.</p>
                        </div>

                        {{-- Wellness Activities --}}
                        <div class="rounded-2xl bg-white shadow-lg ring-2 ring-amber-200 transition hover:shadow-xl hover:ring-amber-300 p-8">
                            <div class="mb-6 flex justify-center">
                                <div class="inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-amber-600 text-white">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                            </div>
                            <h3 class="mb-4 text-lg font-bold text-gray-900 text-center">Wellness Activities</h3>
                            <p class="leading-relaxed text-gray-800">Rejuvenate your mind and body with our wellness programs, including yoga sessions, nature walks, and traditional Filipino healing practices.</p>
                        </div>
                    </div>
                </article>

                {{-- FARM EXPERIENCES --}}
                <article id="experiences" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Explore</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Farm Experiences
                        </h2>
                    </div>

                    <div class="mb-12 rounded-3xl bg-linear-to-br from-amber-50 via-orange-50 to-amber-100 p-12 text-gray-900 shadow-lg md:p-16">
                        <h3 class="mb-4 text-xl font-bold">Educational Farm Tours</h3>
                        <p class="text-lg leading-relaxed">
                            Join our guided tours to learn about sustainable farming practices, crop cultivation, and animal care. Perfect for families, students, and anyone interested in agriculture.
                        </p>
                    </div>

                    <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
                        {{-- Hands-on Farming --}}
                        <div class="rounded-2xl bg-white shadow-lg ring-2 ring-green-200 transition hover:shadow-xl hover:ring-green-300 p-8">
                            <div class="mb-6 flex justify-center">
                                <div class="inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-green-600 text-white">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                </div>
                            </div>
                            <h3 class="mb-4 text-lg font-bold text-gray-900 text-center">Hands-on Farming</h3>
                            <p class="leading-relaxed text-gray-800">Participate in seasonal farm activities like planting, harvesting, and traditional farming methods. Get your hands dirty and learn from experienced farmers.</p>
                        </div>

                        {{-- Animal Interaction --}}
                        <div class="rounded-2xl bg-white shadow-lg ring-2 ring-green-200 transition hover:shadow-xl hover:ring-green-300 p-8">
                            <div class="mb-6 flex justify-center">
                                <div class="inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-green-600 text-white">
                                    <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24"><path d="M11.995 7.23375C10.5455 7.23375 9.35352 6.04079 9.35352 4.59375C9.35352 3.14671 10.5455 1.95375 11.995 1.95375C13.4445 1.95375 14.6365 3.14671 14.6365 4.59375C14.6365 6.04079 13.4445 7.23375 11.995 7.23375ZM8.5589 22.0462C7.68945 22.0462 6.89258 21.5969 6.42773 20.8644C5.42969 19.2644 5.66992 17.2088 6.41016 15.8175C6.62695 15.3979 6.87305 14.9783 7.15625 14.5475C7.24121 14.4163 7.32617 14.2694 7.40137 14.1419C7.46289 14.0294 7.51465 13.9019 7.56055 13.7741C7.62207 13.5894 7.67871 13.4009 7.73340 13.2063C8.07227 11.9306 8.44824 10.5222 9.73145 10.5222H14.2471C15.5303 10.5222 15.9063 11.9306 16.2451 13.2063C16.2998 13.4009 16.3564 13.5894 16.4185 13.7741C16.4639 13.9019 16.5211 14.0294 16.5820 14.1419C16.6572 14.2694 16.7422 14.4163 16.8271 14.5475C17.1103 14.9783 17.3564 15.3979 17.5732 15.8175C18.3135 17.2088 18.5537 19.2644 17.5557 20.8644C17.0908 21.5969 16.2939 22.0462 15.4245 22.0462H8.5589ZM3.27734 7.46484C3.27734 8.95703 2.08008 10.1538 0.587891 10.1538C0.263672 10.1538 0 9.89984 0 9.57422C0 8.08203 1.19727 6.88477 2.69043 6.88477C2.99512 6.88477 3.27734 7.16699 3.27734 7.46484ZM24 9.57422C24 9.89984 23.7363 10.1538 23.4121 10.1538C21.9199 10.1538 20.7227 8.95703 20.7227 7.46484C20.7227 7.16699 21.0049 6.88477 21.3096 6.88477C22.8027 6.88477 24 8.08203 24 9.57422Z"/></svg>
                                </div>
                            </div>
                            <h3 class="mb-4 text-lg font-bold text-gray-900 text-center">Animal Interaction</h3>
                            <p class="leading-relaxed text-gray-800">Meet our farm animals and learn about animal husbandry. From feeding chickens to caring for goats, discover the gentle side of farm life.</p>
                        </div>

                        {{-- Garden Workshops --}}
                        <div class="rounded-2xl bg-white shadow-lg ring-2 ring-green-200 transition hover:shadow-xl hover:ring-green-300 p-8">
                            <div class="mb-6 flex justify-center">
                                <div class="inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-green-600 text-white">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4a1 1 0 011-1h16a1 1 0 011 1v2.757a1 1 0 01-.5.866l-7.5 4.33-7.5-4.33A1 1 0 013 6.757V4z" /></svg>
                                </div>
                            </div>
                            <h3 class="mb-4 text-lg font-bold text-gray-900 text-center">Garden Workshops</h3>
                            <p class="leading-relaxed text-gray-800">Learn about organic gardening, composting, and sustainable landscaping. Take home knowledge and even some plants to start your own garden.</p>
                        </div>
                    </div>
                </article>

                {{-- EVENT SERVICES --}}
                <article id="events" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Celebrate</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Event Services
                        </h2>
                    </div>

                    <div class="rounded-3xl bg-white shadow-lg ring-2 ring-amber-200 p-10 md:p-16">
                        <h3 class="mb-6 text-lg font-bold text-gray-900">Host your special events in our beautiful farm setting. We provide venue spaces and catering services for:</h3>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-4">
                                <span class="mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-white font-bold text-sm">✓</span>
                                <span class="leading-relaxed text-gray-800"><strong>Weddings & Celebrations:</strong> Create magical memories in our natural setting</span>
                            </li>
                            <li class="flex items-start gap-4">
                                <span class="mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-white font-bold text-sm">✓</span>
                                <span class="leading-relaxed text-gray-800"><strong>Corporate Events:</strong> Team building activities and company outings</span>
                            </li>
                            <li class="flex items-start gap-4">
                                <span class="mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-white font-bold text-sm">✓</span>
                                <span class="leading-relaxed text-gray-800"><strong>Family Reunions:</strong> Bring everyone together for quality time</span>
                            </li>
                            <li class="flex items-start gap-4">
                                <span class="mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-600 text-white font-bold text-sm">✓</span>
                                <span class="leading-relaxed text-gray-800"><strong>Educational Tours:</strong> School field trips and learning experiences</span>
                            </li>
                        </ul>
                    </div>
                </article>

                {{-- FACILITIES & AMENITIES --}}
                <article id="amenities" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Comfort</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Facilities & Amenities
                        </h2>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3">
                        <div class="flex flex-col items-center rounded-2xl bg-white shadow-lg ring-2 ring-amber-200 p-8 text-center transition hover:shadow-xl">
                            <div class="mb-4"><svg class="h-8 w-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" /></svg></div>
                            <h3 class="font-bold text-gray-900 mb-2">Free Wi-Fi</h3>
                            <p class="text-sm text-gray-800">Stay connected with complimentary high-speed internet access throughout the farm.</p>
                        </div>

                        <div class="flex flex-col items-center rounded-2xl bg-white shadow-lg ring-2 ring-amber-200 p-8 text-center transition hover:shadow-xl">
                            <div class="mb-4"><svg class="h-8 w-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h12m-12 0l1.117-2.231A2 2 0 0111.234 12h1.532a2 2 0 011.888 1.173l1.286 2.565a2 2 0 001.858 1.062H20m0 0h2m-2 0v2m-6-4h.01M9 20h6" /></svg></div>
                            <h3 class="font-bold text-gray-900 mb-2">Parking</h3>
                            <p class="text-sm text-gray-800">Ample secure parking space for all our guests and visitors.</p>
                        </div>

                        <div class="flex flex-col items-center rounded-2xl bg-white shadow-lg ring-2 ring-amber-200 p-8 text-center transition hover:shadow-xl">
                            <div class="mb-4"><svg class="h-8 w-8 text-amber-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg></div>
                            <h3 class="font-bold text-gray-900 mb-2">Accessibility</h3>
                            <p class="text-sm text-gray-800">Wheelchair accessible facilities and accommodations available.</p>
                        </div>

                        <div class="flex flex-col items-center rounded-2xl bg-white shadow-lg ring-2 ring-amber-200 p-8 text-center transition hover:shadow-xl">
                            <div class="mb-4"><svg class="h-8 w-8 text-amber-600" fill="currentColor" viewBox="0 0 24 24"><path d="M11.995 7.23375C10.5455 7.23375 9.35352 6.04079 9.35352 4.59375C9.35352 3.14671 10.5455 1.95375 11.995 1.95375C13.4445 1.95375 14.6365 3.14671 14.6365 4.59375C14.6365 6.04079 13.4445 7.23375 11.995 7.23375ZM8.5589 22.0462C7.68945 22.0462 6.89258 21.5969 6.42773 20.8644C5.42969 19.2644 5.66992 17.2088 6.41016 15.8175C6.62695 15.3979 6.87305 14.9783 7.15625 14.5475C7.24121 14.4163 7.32617 14.2694 7.40137 14.1419C7.46289 14.0294 7.51465 13.9019 7.56055 13.7741C7.62207 13.5894 7.67871 13.4009 7.73340 13.2063C8.07227 11.9306 8.44824 10.5222 9.73145 10.5222H14.2471C15.5303 10.5222 15.9063 11.9306 16.2451 13.2063C16.2998 13.4009 16.3564 13.5894 16.4185 13.7741C16.4639 13.9019 16.5211 14.0294 16.5820 14.1419C16.6572 14.2694 16.7422 14.4163 16.8271 14.5475C17.1103 14.9783 17.3564 15.3979 17.5732 15.8175C18.3135 17.2088 18.5537 19.2644 17.5557 20.8644C17.0908 21.5969 16.2939 22.0462 15.4245 22.0462H8.5589ZM3.27734 7.46484C3.27734 8.95703 2.08008 10.1538 0.587891 10.1538C0.263672 10.1538 0 9.89984 0 9.57422C0 8.08203 1.19727 6.88477 2.69043 6.88477C2.99512 6.88477 3.27734 7.16699 3.27734 7.46484ZM24 9.57422C24 9.89984 23.7363 10.1538 23.4121 10.1538C21.9199 10.1538 20.7227 8.95703 20.7227 7.46484C20.7227 7.16699 21.0049 6.88477 21.3096 6.88477C22.8027 6.88477 24 8.08203 24 9.57422Z"/></svg></div>
                            <h3 class="font-bold text-gray-900 mb-2">Pet Friendly</h3>
                            <p class="text-sm text-gray-800">Well-behaved pets are welcome in designated areas (advance notice required).</p>
                        </div>

                        <div class="flex flex-col items-center rounded-2xl bg-white shadow-lg ring-2 ring-amber-200 p-8 text-center transition hover:shadow-xl">
                            <div class="mb-4"><svg class="h-8 w-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg></div>
                            <h3 class="font-bold text-gray-900 mb-2">First Aid</h3>
                            <p class="text-sm text-gray-800">Trained staff and first aid facilities available for minor emergencies.</p>
                        </div>

                        <div class="flex flex-col items-center rounded-2xl bg-white shadow-lg ring-2 ring-amber-200 p-8 text-center transition hover:shadow-xl">
                            <div class="mb-4"><svg class="h-8 w-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-9 1h18M4 9h16a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2v-9a2 2 0 012-2z" /></svg></div>
                            <h3 class="font-bold text-gray-900 mb-2">Gift Shop</h3>
                            <p class="text-sm text-gray-800">Take home a piece of Calaia with our selection of farm-fresh products and souvenirs.</p>
                        </div>
                    </div>
                </article>

                {{-- FARM PROGRAMS & WORKSHOPS --}}
                <article id="programs" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Learn</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Farm Programs & Workshops
                        </h2>
                    </div>

                    <div class="grid gap-8 md:grid-cols-2">
                        {{-- Agricultural Workshops --}}
                        <div class="rounded-3xl bg-white shadow-lg ring-2 ring-green-200 p-10 border-l-4 border-green-600">
                            <h3 class="mb-4 text-xl font-bold text-green-900">Agricultural Workshops</h3>
                            <p class="leading-relaxed text-gray-800">Join our hands-on workshops on organic farming, composting, and sustainable agriculture. Learn practical skills you can apply at home.</p>
                        </div>

                        {{-- Educational Programs --}}
                        <div class="rounded-3xl bg-white shadow-lg ring-2 ring-blue-200 p-10 border-l-4 border-blue-600">
                            <h3 class="mb-4 text-xl font-bold text-blue-900">Educational Programs</h3>
                            <p class="leading-relaxed text-gray-800">We offer customized educational programs for schools and groups, teaching about sustainable farming and environmental conservation.</p>
                        </div>
                    </div>
                </article>

                {{-- CTA SECTION --}}
                <article class="mb-24 scroll-mt-24">
                    <div class="rounded-3xl bg-white shadow-lg ring-2 ring-amber-200 p-12 md:p-16 text-center">
                        <h2 class="mb-4 text-3xl font-black text-gray-900">Ready to Experience Calaia Farm?</h2>
                        <p class="mb-8 text-lg leading-relaxed text-gray-800">Contact us to learn more about our services or to book your stay.</p>
                        <a
                            href="{{ route('staycation') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-10 py-4 font-bold text-white shadow-lg transition hover:bg-amber-700 hover:shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
                        >
                            Book Now
                        </a>
                    </div>
                </article>
            </div>
        </section>
        @endif
        <!-- INFORMATION_PAGE_BODY_END -->

        {{-- Floating Buttons --}}
        <x-floating-buttons />
    </div>
</x-page-layout>
