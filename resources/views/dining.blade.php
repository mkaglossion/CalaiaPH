<x-page-layout 
    title="CalaiaPH - Dining"
    description="Calaia Farm — Dining: Experience farm-to-table dining in our elegant mess hall or cook your own fresh ingredients from our farm."
    keywords="Calaia Farm, CalaiaPH, dining, restaurant, farm to table, mess hall, cook your own"
    activeHeader="dining"
    bodyClass="bg-linear-to-b from-[#f5efe7] to-[#ede3d8]"
>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
        <style>
            .swiper-pagination-bullet,
            .swiper-pagination-bullet-active {
                background-color: #a75e3e !important;
            }

            .dining-category-card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .dining-category-card:hover {
                transform: translateY(-4px);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    @endpush

    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$heroTexts ?? []" />

    {{-- Dining Overview Section --}}
    <section class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="max-w-7xl mx-auto">
            <x-section-header
                :title="$diningPageTitle ?? 'Dining at CalaiaPH'"
                :subtitle="$diningPageIntro ?? 'Experience farm-to-table dining in our elegant mess hall or enjoy cooking your own fresh ingredients straight from our farm. Perfect for celebrations, family gatherings, and intimate dinners.'"
            />

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
                {{-- Mess Hall Card --}}
                <div class="dining-category-card bg-white rounded-2xl shadow-xl overflow-hidden group">
                    <div class="relative h-64 sm:h-72 md:h-80 overflow-hidden bg-gray-200">
                        @if(isset($diningCategoryImages['messhall']))
                            <img src="{{ \App\Helpers\ImageHelper::src($diningCategoryImages['messhall']->image_path) }}" alt="Mess Hall" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                        @else
                            <img src="{{ \App\Helpers\ImageHelper::src('images/mess-hall.jpg') }}" alt="Mess Hall" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                        @endif
                        <div class="absolute inset-0 bg-linear-to-t from-black/65 via-black/10 to-transparent"></div>
                        <div class="absolute bottom-5 left-5">
                            <span class="inline-flex items-center px-4 py-2 bg-[#97655A] text-white text-sm font-semibold rounded-full shadow-lg">
                                <i class="fas fa-utensils mr-2"></i> Fine Dining
                            </span>
                        </div>
                    </div>
                    <div class="p-6 md:p-8">
                        <h2 class="text-2xl md:text-3xl font-bold text-[#97655A] mb-4">{{ $messHallTitle ?? 'Mess Hall Dining' }}</h2>
                        <p class="text-sm md:text-base text-gray-600 leading-relaxed">
                            {{ $messHallIntro ?? 'Our spacious and elegant mess hall is perfect for group dining and special occasions. Let our chefs prepare delicious farm-to-table meals using the freshest ingredients from our farm.' }}
                        </p>
                    </div>
                </div>

                {{-- Cook Your Own Card --}}
                <div class="dining-category-card bg-white rounded-2xl shadow-xl overflow-hidden group">
                    <div class="relative h-64 sm:h-72 md:h-80 overflow-hidden bg-gray-200">
                        @if(isset($diningCategoryImages['cook_your_own']))
                            <img src="{{ \App\Helpers\ImageHelper::src($diningCategoryImages['cook_your_own']->image_path) }}" alt="Cook Your Own" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                        @else
                            <img src="{{ \App\Helpers\ImageHelper::src('images/cook-your-own.jpg') }}" alt="Cook Your Own" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                        @endif
                        <div class="absolute inset-0 bg-linear-to-t from-black/65 via-black/10 to-transparent"></div>
                        <div class="absolute bottom-5 left-5">
                            <span class="inline-flex items-center px-4 py-2 bg-orange-500 text-white text-sm font-semibold rounded-full shadow-lg">
                                <i class="fas fa-fire mr-2"></i> Interactive Experience
                            </span>
                        </div>
                    </div>
                    <div class="p-6 md:p-8">
                        <h2 class="text-2xl md:text-3xl font-bold text-[#97655A] mb-4">{{ $cookYourOwnTitle ?? 'Cook Your Own Experience' }}</h2>
                        <p class="text-sm md:text-base text-gray-600 leading-relaxed">
                            {{ $cookYourOwnIntro ?? 'Enjoy cooking your own fresh ingredients harvested directly from our farm. Choose premium, locally sourced ingredients and create your own meal with guidance from our team.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Call to Action - Messenger Reservation --}}
    <section class="container mx-auto px-4 sm:px-6 lg:px-8 pb-16 md:pb-20">
        <div class="max-w-4xl mx-auto">
            <div class="bg-linear-to-r from-[#97655A] to-[#8f5d54] rounded-2xl p-6 sm:p-8 md:p-12 text-white text-center shadow-xl">
                <i class="fas fa-utensils text-4xl md:text-5xl opacity-80 mb-4 block"></i>

                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4">
                    {{ $ctaTitle ?? 'Ready to Dine With Us?' }}
                </h2>

                <p class="text-base md:text-lg mb-8 opacity-90 max-w-2xl mx-auto">
                    {{ $ctaIntro ?? 'Message us to reserve your dining experience. We can accommodate lunch, dinner, group events, and special arrangements.' }}
                </p>

                <a href="https://m.me/calaiaph"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex w-full sm:w-auto items-center justify-center px-6 md:px-8 py-4 bg-[#0084ff] hover:bg-[#0073e0] text-white font-semibold rounded-xl transition-all duration-300 gap-3 shadow-lg text-base md:text-lg">
                    <i class="fab fa-facebook-messenger text-xl md:text-2xl"></i>
                    {{ $ctaButton ?? 'Make a Reservation on Messenger' }}
                </a>

                <p class="text-xs sm:text-sm mt-6 opacity-75">
                    {{ $ctaNote ?? 'Available for lunch and dinner reservations. Message us for inquiries.' }}
                </p>
            </div>
        </div>
    </section>

    <x-floating-buttons />
</x-page-layout>
