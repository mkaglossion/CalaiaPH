{{-- resources/views/pages/sustainability.blade.php --}}
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

        <!-- INFORMATION_PAGE_BODY_START -->
        @if($informationPage->hasStoredBody())
            {!! $informationPage->renderedBodyHtml() !!}
        @else
        {{-- CONTENT --}}
        <section class="bg-white py-12 sm:py-16 pb-0">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

                {{-- Our Environmental Commitment --}}
                <article id="commitment" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Foundation</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Our Environmental Commitment
                        </h2>
                    </div>

                    <div class="space-y-6 rounded-3xl bg-linear-to-br from-amber-50 via-orange-50 to-amber-100 p-12 text-gray-900 shadow-lg md:p-16">
                        <p class="text-lg leading-relaxed font-semibold">
                            At Calaia Farm, sustainability isn't just a buzzword—it's the foundation of everything we do. We believe that responsible farming practices can heal the earth while providing for our community.
                        </p>
                        <p class="text-lg leading-relaxed">
                            Our approach integrates traditional wisdom with modern sustainable techniques to create a model that others can follow.
                        </p>
                    </div>
                </article>

                {{-- Sustainability Pillars --}}
                <article id="pillars" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Key Metrics</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Our Sustainability Pillars
                        </h2>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-4">
                        <div class="rounded-2xl bg-linear-to-br from-green-500 to-green-700 p-10 text-center text-white shadow-lg transition hover:shadow-xl hover:scale-105">
                            <h3 class="mb-4 text-4xl font-black">100%</h3>
                            <p class="text-base font-semibold">Organic Farming</p>
                        </div>
                        <div class="rounded-2xl bg-linear-to-br from-green-500 to-green-700 p-10 text-center text-white shadow-lg transition hover:shadow-xl hover:scale-105">
                            <h3 class="mb-4 text-4xl font-black">Zero</h3>
                            <p class="text-base font-semibold">Chemical Pesticides</p>
                        </div>
                        <div class="rounded-2xl bg-linear-to-br from-green-500 to-green-700 p-10 text-center text-white shadow-lg transition hover:shadow-xl hover:scale-105">
                            <h3 class="mb-4 text-4xl font-black">50%</h3>
                            <p class="text-base font-semibold">Water Conservation</p>
                        </div>
                        <div class="rounded-2xl bg-linear-to-br from-green-500 to-green-700 p-10 text-center text-white shadow-lg transition hover:shadow-xl hover:scale-105">
                            <h3 class="mb-4 text-4xl font-black">80%</h3>
                            <p class="text-base font-semibold">Local Sourcing</p>
                        </div>
                    </div>
                </article>

                {{-- Key Sustainability Initiatives --}}
                <article id="initiatives" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">What We Do</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Key Sustainability Initiatives
                        </h2>
                    </div>

                    <div class="grid gap-8 sm:grid-cols-2">
                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg ring-2 ring-green-200 transition hover:shadow-xl hover:ring-green-300">
                            <div class="h-48 w-full overflow-hidden bg-linear-to-br from-green-200 to-green-300"></div>
                            <div class="p-8">
                                <h3 class="mb-4 text-lg font-bold text-gray-900">Organic Farming Practices</h3>
                                <p class="leading-relaxed text-gray-800">We use only natural methods to maintain soil health and control pests. Our composting system returns nutrients to the soil, creating a closed-loop system.</p>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg ring-2 ring-green-200 transition hover:shadow-xl hover:ring-green-300">
                            <div class="h-48 w-full overflow-hidden bg-linear-to-br from-blue-200 to-blue-300"></div>
                            <div class="p-8">
                                <h3 class="mb-4 text-lg font-bold text-gray-900">Water Conservation</h3>
                                <p class="leading-relaxed text-gray-800">Our innovative rainwater harvesting and drip irrigation systems reduce water usage by half while ensuring our crops get the moisture they need.</p>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg ring-2 ring-green-200 transition hover:shadow-xl hover:ring-green-300">
                            <div class="h-48 w-full overflow-hidden bg-linear-to-br from-emerald-200 to-emerald-300"></div>
                            <div class="p-8">
                                <h3 class="mb-4 text-lg font-bold text-gray-900">Biodiversity Enhancement</h3>
                                <p class="leading-relaxed text-gray-800">We've planted native trees and created habitats that support local wildlife. Our farm has become a sanctuary for birds, butterflies, and beneficial insects.</p>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg ring-2 ring-green-200 transition hover:shadow-xl hover:ring-green-300">
                            <div class="h-48 w-full overflow-hidden bg-linear-to-br from-amber-200 to-amber-300"></div>
                            <div class="p-8">
                                <h3 class="mb-4 text-lg font-bold text-gray-900">Renewable Energy</h3>
                                <p class="leading-relaxed text-gray-800">Solar panels power our facilities, and we're working towards energy independence. Our goal is to generate more energy than we consume.</p>
                            </div>
                        </div>
                    </div>
                </article>

                {{-- Waste Management & Recycling --}}
                <article id="waste" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Environment</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Waste Management & Recycling
                        </h2>
                    </div>

                    <div class="mb-10 rounded-3xl bg-linear-to-br from-green-50 to-emerald-50 border-l-4 border-green-600 p-8 shadow-lg">
                        <h3 class="mb-4 text-lg font-bold text-green-900">Zero Waste Initiative</h3>
                        <p class="leading-relaxed text-gray-800">Our comprehensive waste management system ensures that nothing goes to landfill. Food waste becomes compost, packaging is reused or recycled, and we continuously seek ways to minimize our environmental impact.</p>
                    </div>

                    <h3 class="mb-8 text-xl font-bold text-gray-900">Waste Reduction Strategies:</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-4 rounded-lg bg-white p-4 shadow-sm">
                            <span class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-600 text-white font-bold">♻</span>
                            <span class="leading-relaxed text-gray-800"><strong>Composting:</strong> All organic waste is composted and returned to the soil</span>
                        </li>
                        <li class="flex items-start gap-4 rounded-lg bg-white p-4 shadow-sm">
                            <span class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-600 text-white font-bold">♻</span>
                            <span class="leading-relaxed text-gray-800"><strong>Recycling:</strong> Paper, plastic, glass, and metal are sorted and recycled</span>
                        </li>
                        <li class="flex items-start gap-4 rounded-lg bg-white p-4 shadow-sm">
                            <span class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-600 text-white font-bold">♻</span>
                            <span class="leading-relaxed text-gray-800"><strong>Reuse:</strong> Packaging materials are reused whenever possible</span>
                        </li>
                        <li class="flex items-start gap-4 rounded-lg bg-white p-4 shadow-sm">
                            <span class="mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-600 text-white font-bold">♻</span>
                            <span class="leading-relaxed text-gray-800"><strong>Food Recovery:</strong> Surplus produce is donated to local communities</span>
                        </li>
                    </ul>
                </article>

                {{-- Community & Education --}}
                <article id="education" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Community</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Community & Education
                        </h2>
                    </div>

                    <div class="mb-12 rounded-3xl bg-linear-to-br from-green-50 via-emerald-50 to-green-100 p-12 shadow-lg md:p-16">
                        <h3 class="mb-4 text-xl font-bold text-gray-900">Environmental Education</h3>
                        <p class="text-lg leading-relaxed text-gray-800">We believe that education is key to creating lasting change. Through our workshops, farm tours, and school programs, we teach visitors about sustainable agriculture and environmental stewardship.</p>
                    </div>

                    <h3 class="mb-8 text-xl font-bold text-gray-900">Educational Programs:</h3>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-3 rounded-lg bg-white p-4 shadow-sm">
                            <svg class="h-5 w-5 shrink-0 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 10a8 8 0 008-8v8h8a8 8 0 11-16 0z"/>
                                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                            </svg>
                            <span class="leading-relaxed text-gray-800"><strong>Farm Tours:</strong> Guided tours explaining our sustainable practices</span>
                        </li>
                        <li class="flex items-center gap-3 rounded-lg bg-white p-4 shadow-sm">
                            <svg class="h-5 w-5 shrink-0 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 10a8 8 0 008-8v8h8a8 8 0 11-16 0z"/>
                                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                            </svg>
                            <span class="leading-relaxed text-gray-800"><strong>School Programs:</strong> Educational visits for students of all ages</span>
                        </li>
                        <li class="flex items-center gap-3 rounded-lg bg-white p-4 shadow-sm">
                            <svg class="h-5 w-5 shrink-0 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 10a8 8 0 008-8v8h8a8 8 0 11-16 0z"/>
                                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                            </svg>
                            <span class="leading-relaxed text-gray-800"><strong>Workshops:</strong> Hands-on learning about organic farming and conservation</span>
                        </li>
                        <li class="flex items-center gap-3 rounded-lg bg-white p-4 shadow-sm">
                            <svg class="h-5 w-5 shrink-0 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 10a8 8 0 008-8v8h8a8 8 0 11-16 0z"/>
                                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                            </svg>
                            <span class="leading-relaxed text-gray-800"><strong>Community Outreach:</strong> Free seminars for local farmers and residents</span>
                        </li>
                    </ul>
                </article>

                {{-- Local Community Support --}}
                <article id="community" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Local Impact</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Local Community Support
                        </h2>
                    </div>

                    <div class="mb-10 rounded-3xl bg-linear-to-br from-green-50 to-emerald-50 border-l-4 border-green-600 p-10 shadow-lg">
                        <p class="text-lg leading-relaxed text-gray-800">We are deeply committed to supporting our local community. By sourcing from nearby farmers, employing local residents, and sharing our knowledge, we create a network of sustainability that extends beyond our farm boundaries.</p>
                    </div>

                    <h3 class="mb-8 text-xl font-bold text-gray-900">Community Initiatives:</h3>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-3 rounded-lg bg-white p-4 shadow-sm">
                            <svg class="h-5 w-5 shrink-0 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v4h8v-4zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                            <span class="leading-relaxed text-gray-800">Supporting local farmers through fair trade practices</span>
                        </li>
                        <li class="flex items-center gap-3 rounded-lg bg-white p-4 shadow-sm">
                            <svg class="h-5 w-5 shrink-0 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v4h8v-4zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                            <span class="leading-relaxed text-gray-800">Creating employment opportunities for community members</span>
                        </li>
                        <li class="flex items-center gap-3 rounded-lg bg-white p-4 shadow-sm">
                            <svg class="h-5 w-5 shrink-0 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v4h8v-4zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                            <span class="leading-relaxed text-gray-800">Sharing resources and knowledge with neighboring farms</span>
                        </li>
                        <li class="flex items-center gap-3 rounded-lg bg-white p-4 shadow-sm">
                            <svg class="h-5 w-5 shrink-0 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v4h8v-4zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                            <span class="leading-relaxed text-gray-800">Contributing to local environmental conservation efforts</span>
                        </li>
                    </ul>
                </article>

                {{-- Measuring Our Impact --}}
                <article id="impact" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Proof</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Measuring Our Impact
                        </h2>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-4">
                        <div class="rounded-2xl bg-linear-to-br from-green-500 to-green-700 p-10 text-center text-white shadow-lg transition hover:shadow-xl hover:scale-105">
                            <h3 class="mb-4 text-4xl font-black">5 Yrs</h3>
                            <p class="text-base font-semibold">Carbon Neutral</p>
                        </div>
                        <div class="rounded-2xl bg-linear-to-br from-green-500 to-green-700 p-10 text-center text-white shadow-lg transition hover:shadow-xl hover:scale-105">
                            <h3 class="mb-4 text-4xl font-black">1000+</h3>
                            <p class="text-base font-semibold">Trees Planted</p>
                        </div>
                        <div class="rounded-2xl bg-linear-to-br from-green-500 to-green-700 p-10 text-center text-white shadow-lg transition hover:shadow-xl hover:scale-105">
                            <h3 class="mb-4 text-4xl font-black">500+</h3>
                            <p class="text-base font-semibold">Students Educated</p>
                        </div>
                        <div class="rounded-2xl bg-linear-to-br from-green-500 to-green-700 p-10 text-center text-white shadow-lg transition hover:shadow-xl hover:scale-105">
                            <h3 class="mb-4 text-4xl font-black">25</h3>
                            <p class="text-base font-semibold">Jobs Created</p>
                        </div>
                    </div>
                </article>

                {{-- Future Goals --}}
                <article id="future" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Vision</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Future Goals
                        </h2>
                    </div>

                    <div class="rounded-3xl bg-linear-to-br from-green-50 to-emerald-50 border-l-4 border-green-600 p-10 shadow-lg">
                        <h3 class="mb-8 text-xl font-bold text-green-900">Our 2030 Sustainability Targets</h3>
                        <ul class="space-y-4">
                            <li class="flex items-center gap-3">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center text-green-600 font-bold text-sm">✓</span>
                                <span class="leading-relaxed text-gray-800">Achieve complete energy independence through renewable sources</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center text-green-600 font-bold text-sm">✓</span>
                                <span class="leading-relaxed text-gray-800">Expand organic farming practices to 100 hectares</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center text-green-600 font-bold text-sm">✓</span>
                                <span class="leading-relaxed text-gray-800">Educate 5,000 students about sustainable agriculture</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center text-green-600 font-bold text-sm">✓</span>
                                <span class="leading-relaxed text-gray-800">Achieve zero-waste operations</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center text-green-600 font-bold text-sm">✓</span>
                                <span class="leading-relaxed text-gray-800">Create a replicable model for sustainable farming in the Philippines</span>
                            </li>
                        </ul>
                    </div>
                </article>

                {{-- Get Involved --}}
                <article id="involve" class="mb-24 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-green-700">Action</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Get Involved
                        </h2>
                    </div>

                    <p class="mb-10 text-lg leading-relaxed text-gray-800">We believe that sustainability is a collective effort. Here are ways you can join us in protecting our environment:</p>

                    <div class="rounded-3xl bg-linear-to-br from-green-50 via-emerald-50 to-green-100 p-12 shadow-lg md:p-16">
                        <h3 class="mb-8 text-xl font-bold text-gray-900">Ways to Participate:</h3>
                        <ul class="space-y-4">
                            <li class="flex items-center gap-3">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-green-600 text-white font-bold text-xs">1</span>
                                <div>
                                    <p class="font-bold text-gray-900 mb-1">Volunteer</p>
                                    <p class="leading-relaxed text-gray-800">Join our farm work days and conservation projects</p>
                                </div>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-green-600 text-white font-bold text-xs">2</span>
                                <div>
                                    <p class="font-bold text-gray-900 mb-1">Learn</p>
                                    <p class="leading-relaxed text-gray-800">Attend our workshops and educational programs</p>
                                </div>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-green-600 text-white font-bold text-xs">3</span>
                                <div>
                                    <p class="font-bold text-gray-900 mb-1">Support</p>
                                    <p class="leading-relaxed text-gray-800">Choose local, sustainable products and services</p>
                                </div>
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-green-600 text-white font-bold text-xs">4</span>
                                <div>
                                    <p class="font-bold text-gray-900 mb-1">Share</p>
                                    <p class="leading-relaxed text-gray-800">Spread the word about sustainable farming practices</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-16 text-center">
                        <h3 class="mb-4 text-2xl font-black text-gray-900">Together, we can create a sustainable future</h3>
                        <p class="mb-8 text-lg leading-relaxed text-gray-800">Every action, no matter how small, contributes to the health of our planet.</p>
                        <a
                            href="{{ route('staycation') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-green-600 px-10 py-4 font-bold text-white shadow-lg transition hover:bg-green-700 hover:shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-400"
                        >
                            Start Your Journey
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
