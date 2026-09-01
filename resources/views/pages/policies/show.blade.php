<x-page-layout
    :title="$policyPage->meta_title"
    :description="$policyPage->meta_description"
    :keywords="$policyPage->meta_keywords"
    :active-header="$policyPage->slug"
>
    <div class="min-h-screen bg-white">
        @if($policyPage->shouldShowHero())
        <section class="relative h-screen w-full overflow-hidden bg-linear-to-br from-amber-900 via-amber-800 to-amber-950">
            <div class="relative z-10 flex h-full flex-col items-center justify-center px-4 text-center">
                <div class="max-w-4xl space-y-8">
                    @if(filled($policyPage->hero_eyebrow))
                        <p class="text-sm font-bold uppercase tracking-[0.3em] text-amber-200">
                            {{ $policyPage->hero_eyebrow }}
                        </p>
                    @endif

                    <h1 class="text-5xl font-black text-white drop-shadow-lg sm:text-6xl md:text-7xl">
                        <span class="text-amber-300">{{ $policyPage->hero_title }}</span>
                    </h1>

                    @if(filled($policyPage->hero_description))
                        @php($heroLines = preg_split('/\R/u', trim($policyPage->hero_description)) ?: [])
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

        {!! $policyPage->renderedBodyHtml() !!}

        <x-floating-buttons />
    </div>
</x-page-layout>
