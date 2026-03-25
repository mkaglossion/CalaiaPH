<x-page-layout 
    title="Vacation Packages - CalaiaPH"
    description="Explore our curated collection of vacation packages at CalaiaPH. Discover unforgettable experiences tailored for your perfect escape."
    keywords="vacation packages, holiday packages, travel packages, farm stay packages, day tour packages, CalaiaPH, Philippines travel"
    activeHeader="packages"
    bodyClass="bg-[#f5efe7]"
>
    @push('styles')
        <style>
            .photo-clarity {
                image-rendering: auto;
                -webkit-font-smoothing: antialiased;
                transform: translateZ(0);
                backface-visibility: hidden;
                will-change: transform;
            }

            /* Tilt Card Animation */
            .tilt-card {
                transform-style: preserve-3d;
                perspective: 1200px;
                transition: transform .25s ease, box-shadow .25s ease;
            }
            .tilt-card:hover {
                transform: rotateX(6deg) rotateY(-6deg) translateY(-6px) scale(1.02);
                box-shadow: 0 16px 35px rgba(0,0,0,0.18);
            }

            .package-image {
                height: 280px;
                object-fit: cover;
                overflow: hidden;
            }
        </style>
    @endpush

    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

    <main class="py-16 md:py-20 lg:py-24" style="background: linear-gradient(135deg, #f5efe7 0%, #f9f6f3 100%);">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">

            {{-- Section Header --}}
            <div class="mb-12 text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 mb-3">
                    Unforgettable <span class="text-brand">Experiences</span>
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Discover our curated collection of vacation packages designed to give you the perfect escape
                </p>
                <div class="flex gap-1 justify-center mt-6">
                    <div class="h-1 w-8 rounded-full bg-brand"></div>
                    <div class="h-1 w-8 rounded-full bg-brand/50"></div>
                    <div class="h-1 w-8 rounded-full bg-brand/25"></div>
                </div>
            </div>

            @if($packages->count() > 0)
                <!-- Packages Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 auto-rows-max">
                    @foreach($packages as $package)
                        <div class="h-full">
                            <x-package-card :package="$package" variant="frontend" />
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-24">
                    <div class="mb-6 inline-block">
                        <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="bi bi-inbox text-gray-400 text-4xl"></i>
                        </div>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">No Packages Available</h3>
                    <p class="text-gray-600">Check back soon for new exclusive vacation packages!</p>
                </div>
            @endif

        </div>
    </main>
</x-page-layout>
