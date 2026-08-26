<x-page-layout
    title="CalaiaPH - Packages"
    description="Explore CalaiaPH package options and featured stays, bundled for a smoother booking experience."
    keywords="packages, day packages, stay packages, CalaiaPH, Philippines packages"
    activeHeader="activities"
    bodyClass="bg-[#f5efe7]"
>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @endpush

    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$heroTexts" />

    <main id="main">
        {{-- ========= FEATURED PACKAGES ========= --}}
        <section class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <div class="max-w-7xl mx-auto">
                <x-section-header
                    :title="$packagesSectionTitle ?? 'Featured Packages'"
                    :subtitle="$packagesSectionIntro ?? 'Browse our curated packages and choose the one that fits your stay.'"
                    dividerClass="bg-brand"
                />

                @if($packages->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 auto-rows-max">
                        @foreach($packages as $package)
                            <div class="h-full">
                                <x-package-card :package="$package" variant="frontend" />
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16">
                        <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">No Packages Available</h3>
                        <p class="text-gray-600">Please check back soon for new package offerings.</p>
                    </div>
                @endif
            </div>
        </section>
    </main>

    {{-- Floating Buttons --}}
    <x-floating-buttons />

</x-page-layout>
