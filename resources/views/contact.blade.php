<x-page-layout
    :title="$informationPage->meta_title"
    :description="$informationPage->meta_description"
    :keywords="$informationPage->meta_keywords"
    :active-header="$informationPage->slug"
>
    @push('styles')
        <style>
            .contact-page-content #contact-heading {
                font-size: clamp(2rem, 4vw, 3rem) !important;
                line-height: 1.15 !important;
            }
        </style>
    @endpush

    {{-- Centralized Hero Banner --}}
    <div class="min-h-screen bg-white">
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
        <div class="contact-page-content">
            @if($informationPage->hasStoredBody())
                {!! $informationPage->renderedBodyHtml() !!}
            @else
            {{-- CONTENT --}}
            <section class="bg-white py-12 sm:py-16 pb-0">
                <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    {{-- SECTION: Visit or Contact Us --}}
                    <x-contact-section />
                </div>
            </section>
            @endif
        </div>
        <!-- INFORMATION_PAGE_BODY_END -->

        {{-- Floating Buttons --}}
        <x-floating-buttons />
    </div>
</x-page-layout>
