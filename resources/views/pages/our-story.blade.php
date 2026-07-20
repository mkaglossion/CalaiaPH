{{-- resources/views/pages/our-story.blade.php --}}
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
                        <div class="inline-block rounded-full border-2 border-amber-300 bg-white/10 backdrop-blur px-6 py-3 text-sm font-bold uppercase tracking-widest text-amber-100">
                            {{ $informationPage->hero_eyebrow }}
                        </div>
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

                    <div class="flex flex-col justify-center gap-4 pt-8 sm:flex-row">
                        <a
                            href="#beginning"
                            class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-8 py-4 font-bold text-white shadow-lg transition hover:bg-amber-600 hover:shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400"
                        >
                            Read Our Story
                        </a>
                        <a
                            href="{{ route('staycation') }}"
                            class="inline-flex items-center justify-center rounded-lg border-2 border-white bg-white/10 px-8 py-4 font-bold text-white transition hover:bg-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        >
                            Book a Stay
                        </a>
                    </div>
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
                {{-- The Beginning --}}
                <article id="beginning" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-amber-700">Chapter One</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            The Beginning
                        </h2>
                    </div>

                    <div class="space-y-8 rounded-3xl bg-linear-to-br from-amber-50 via-orange-50 to-amber-100 p-12 text-gray-900 shadow-lg md:p-16">
                        <p class="text-lg leading-relaxed font-semibold">
                            Calaia Farm was born from a simple dream: to create a space where people could reconnect with nature while supporting sustainable farming practices.
                        </p>
                        <p class="text-lg leading-relaxed">
                            The ownwers founded, what started as a small family farm in Calaca, Batangas has grown into a beloved nature park that welcomes visitors from all walks of life.
                        </p>
                        <p class="flex items-center gap-3 text-xl font-bold text-amber-900">
                            <span>✨</span> A vision turned into reality, one harvest at a time.
                        </p>
                    </div>
                </article>

                {{-- Journey Timeline --}}
                @php
                    $milestones = [
                        ['year' => '2018', 'title' => 'The Foundation', 'description' => 'Our founders began with a vision to transform underutilized land into a productive farm.'],
                        ['year' => '2019', 'title' => 'First Crops', 'description' => 'We planted our first organic vegetables and fruit trees, focusing on traditional Filipino crops.'],
                        ['year' => '2020', 'title' => 'Community Outreach', 'description' => 'Despite challenges, we opened our doors offering educational farm tours and workshops.'],
                        ['year' => '2021', 'title' => 'Expansion', 'description' => 'We expanded to include accommodation and activity areas for immersive farm experiences.'],
                        ['year' => '2022', 'title' => 'Recognition', 'description' => 'Received recognition for environmental stewardship and community impact.'],
                        ['year' => '2024', 'title' => 'Growth & Innovation', 'description' => 'Continuing to grow and evolve, always true to sustainability, education, and community.'],
                    ];
                @endphp

                <article id="journey" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-amber-700">Our History</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Our Journey
                        </h2>
                    </div>

                    <div class="space-y-6">
                        @foreach ($milestones as $milestone)
                            <div class="group border-l-4 border-amber-600 bg-linear-to-r from-amber-50 via-white to-orange-50 p-10 shadow-md transition hover:shadow-xl hover:border-amber-700 hover:bg-linear-to-r hover:from-amber-100 hover:via-white hover:to-orange-100">
                                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-baseline sm:gap-4">
                                    <span class="text-4xl font-black text-amber-700">{{ $milestone['year'] }}</span>
                                    <h3 class="text-2xl font-bold text-gray-900">{{ $milestone['title'] }}</h3>
                                </div>
                                <p class="text-lg leading-relaxed text-gray-800">{{ $milestone['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </article>

                {{-- Mission & Vision --}}
                <article id="mission" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-amber-700">Our Purpose</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Mission & Vision
                        </h2>
                    </div>

                    <div class="grid gap-10 md:grid-cols-2">
                        <div class="rounded-2xl bg-white p-6 shadow-lg ring-2 ring-amber-200 transition hover:shadow-xl hover:ring-amber-300">
                            <h3 class="mb-4 text-xl font-bold text-gray-900">Our Mission</h3>
                            <p class="leading-relaxed text-gray-800">
                                To cultivate a sustainable future by connecting people with nature through organic farming, education, and authentic farm experiences that nourish both body and soul.
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white p-6 shadow-lg ring-2 ring-amber-200 transition hover:shadow-xl hover:ring-amber-300">
                            <h3 class="mb-4 text-xl font-bold text-gray-900">Our Vision</h3>
                            <p class="leading-relaxed text-gray-800">
                                To be a leading example of sustainable agriculture in the Philippines, inspiring communities to embrace environmentally conscious practices while preserving our agricultural heritage.
                            </p>
                        </div>
                    </div>
                </article>

                {{-- Core Values --}}
                @php
                    $values = [
                        ['icon' => 'fa-leaf', 'title' => 'Sustainability', 'desc' => 'We are committed to environmental stewardship and sustainable farming practices that protect our planet for future generations.'],
                        ['icon' => 'fa-users', 'title' => 'Community', 'desc' => 'We believe in building strong community connections and supporting local farmers and artisans in the Calaca region.'],
                        ['icon' => 'fa-graduation-cap', 'title' => 'Education', 'desc' => 'We are passionate about sharing knowledge about sustainable agriculture and environmental conservation with visitors and locals alike.'],
                        ['icon' => 'fa-heart', 'title' => 'Authenticity', 'desc' => 'We stay true to our roots, offering genuine farm experiences that honor Filipino agricultural traditions passed down by Gilson and Georgia.'],
                    ];
                @endphp

                <article id="values" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-amber-700">What We Stand For</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Our Values
                        </h2>
                    </div>

                    <div class="grid gap-10 sm:grid-cols-2 md:grid-cols-4">
                        @foreach ($values as $value)
                            <div class="group rounded-2xl bg-white p-6 shadow-lg ring-2 ring-amber-100 transition hover:shadow-xl hover:ring-amber-200">
                                <div class="mb-8 flex justify-center">
                                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-linear-to-br from-amber-500 to-amber-700 shadow-lg transition group-hover:scale-110">
                                        <i class="fas {{ $value['icon'] }} text-4xl text-white"></i>
                                    </div>
                                </div>
                                <h4 class="mb-4 text-center text-xl font-bold text-gray-900">{{ $value['title'] }}</h4>
                                <p class="text-center leading-relaxed text-gray-700">{{ $value['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </article>

                {{-- Impact --}}
                @php
                    $impacts = [
                        ['icon' => '👥', 'text' => 'Welcomed 10,000+ visitors to our farm'],
                        ['icon' => '🎓', 'text' => 'Educated hundreds of students about sustainable farming'],
                        ['icon' => '🤝', 'text' => 'Supported local farmers through community programs'],
                        ['icon' => '🌱', 'text' => 'Maintained 100% organic farming practices'],
                        ['icon' => '🍃', 'text' => 'Contributed to local biodiversity conservation'],
                        ['icon' => '💼', 'text' => 'Created employment for 50+ local residents'],
                    ];
                @endphp

                <article id="impact" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-amber-700">Making A Difference</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Our Impact
                        </h2>
                    </div>

                    <div class="rounded-3xl bg-linear-to-br from-amber-50 via-white to-orange-50 p-12 shadow-lg ring-2 ring-amber-200 md:p-16">
                        <p class="mb-10 text-lg font-bold text-gray-900">Since our founding, Calaia Farm has:</p>

                        <ul class="space-y-5">
                            <li class="flex items-start gap-4">
                                <span class="text-2xl font-bold text-amber-700 shrink-0">✓</span>
                                <span class="text-gray-800">Welcomed over 10,000 visitors to our farm</span>
                            </li>
                            <li class="flex items-start gap-4">
                                <span class="text-2xl font-bold text-amber-700 shrink-0">✓</span>
                                <span class="text-gray-800">Educated hundreds of students about sustainable farming</span>
                            </li>
                            <li class="flex items-start gap-4">
                                <span class="text-2xl font-bold text-amber-700 shrink-0">✓</span>
                                <span class="text-gray-800">Supported local farmers through our community programs</span>
                            </li>
                            <li class="flex items-start gap-4">
                                <span class="text-2xl font-bold text-amber-700 shrink-0">✓</span>
                                <span class="text-gray-800">Maintained organic farming practices across 100% of our land</span>
                            </li>
                            <li class="flex items-start gap-4">
                                <span class="text-2xl font-bold text-amber-700 shrink-0">✓</span>
                                <span class="text-gray-800">Contributed to local biodiversity conservation efforts</span>
                            </li>
                            <li class="flex items-start gap-4">
                                <span class="text-2xl font-bold text-amber-700 shrink-0">✓</span>
                                <span class="text-gray-800">Created employment opportunities for local residents</span>
                            </li>
                        </ul>
                    </div>
                </article>

                {{-- Looking Forward --}}
                <article id="future" class="mb-32 scroll-mt-24">
                    <div class="mb-16">
                        <p class="mb-4 text-sm font-bold uppercase tracking-widest text-amber-700">What's Next</p>
                        <h2 class="text-2xl font-black text-gray-900 md:text-3xl">
                            Looking Forward
                        </h2>
                    </div>

                    <div class="space-y-8 rounded-3xl bg-linear-to-br from-white via-amber-50 to-orange-50 p-12 shadow-lg ring-2 ring-amber-200 md:p-16">
                        <p class="leading-relaxed font-semibold text-gray-900">
                            As we continue to grow, we remain committed to our founding principles. We are constantly exploring new ways to enhance our sustainability efforts, expand our educational programs, and create more meaningful experiences for our visitors.
                        </p>
                        <p class="leading-relaxed text-gray-900">
                            Our story is still being written, and <span class="font-black text-amber-700">we invite you to be a part of it</span>. Whether you're visiting for a day, staying overnight, or joining one of our workshops, you become part of the Calaia Farm family and our ongoing journey toward a more sustainable future.
                        </p>
                    </div>
                </article>

                {{-- CTA --}}
                <section class="mb-24">
                    <div class="rounded-xl bg-linear-to-r from-amber-900 to-amber-800 p-12 text-white md:p-16">
                        <div class="text-center">
                            <h3 class="mb-6 text-3xl font-black drop-shadow-lg md:text-4xl">
                                ✨ Join Our Growing Community
                            </h3>
                            <p class="mb-10 text-lg font-semibold leading-relaxed text-white drop-shadow-md md:text-xl">
                                Every visitor, every stay, and every interaction helps us continue our mission. Be part of something meaningful.
                            </p>

                            <div class="flex flex-col justify-center gap-4 sm:flex-row">
                                <a
                                    href="{{ route('staycation') }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-white px-10 py-4 font-bold text-amber-900 shadow-lg transition hover:bg-gray-100 hover:shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                                >
                                    Book Your Stay
                                </a>
                                <a
                                    href="{{ route('activities') }}"
                                    class="inline-flex items-center justify-center rounded-lg border-2 border-white bg-white/10 px-10 py-4 font-bold text-white transition hover:bg-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                                >
                                    Explore Activities
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>
        @endif
        <!-- INFORMATION_PAGE_BODY_END -->
        {{-- Floating Buttons --}}
        <x-floating-buttons />    </div>
</x-page-layout>
