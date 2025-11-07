{{-- Day Tour Cards Component --}}
<div class="space-y-16">
    {{-- Packages Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
        @foreach($packages as $package)
        <div class="bg-white rounded-3xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-3 group border border-gray-100">
            <div class="relative overflow-hidden">
                <img
                    src="{{ asset($package['image']) }}"
                    class="h-72 md:h-80 w-full object-cover group-hover:scale-110 transition-transform duration-700"
                    alt="{{ $package['title'] }} package"
                    loading="lazy"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </div>

            <div class="p-6 md:p-8">
                <h3 class="text-xl md:text-2xl lg:text-3xl font-bold text-center mb-6 text-[#8f5d54] font-gotham">
                    {{ $package['title'] }}
                </h3>

                <ul class="space-y-4 mb-8">
                    @foreach($package['features'] as $feature)
                    <li class="flex items-start text-gray-700">
                        <i class="bi bi-check-circle-fill text-green-500 mr-4 mt-0.5 flex-shrink-0 text-lg"></i>
                        <span class="text-sm md:text-base leading-relaxed">{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>

                <div class="flex justify-center">
                    <button
                        type="button"
                        class="bg-[#8f5d54] text-white px-8 py-4 rounded-2xl hover:bg-[#a0675a] transition-all duration-300 font-semibold focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:ring-offset-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                        data-title="{{ $package['title'] }}"
                        data-details="{{ json_encode($package['details']) }}"
                        data-gallery="{{ json_encode(array_map(function($img) { return asset($img); }, $package['gallery'])) }}"
                    >
                        LEARN MORE
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Information Section --}}
    <div class="bg-gradient-to-r from-[#6b9691] to-[#7ba08a] text-white rounded-3xl p-8 md:p-10 shadow-xl">
        <div class="text-center mb-8">
            <h4 class="text-2xl md:text-3xl font-bold mb-4 flex items-center justify-center font-gotham">
                <i class="bi bi-info-circle-fill mr-4 text-3xl"></i>
                Important Information
            </h4>
            <p class="text-lg opacity-90">Everything you need to know before your visit</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach([
                ['icon' => 'bi-clock', 'text' => 'Operating hours: 8:00 AM - 5:00 PM daily'],
                ['icon' => 'bi-calendar-check', 'text' => 'Advance booking recommended for guaranteed slots'],
                ['icon' => 'bi-person', 'text' => 'Children under 3 years old enter for free'],
                ['icon' => 'bi-people', 'text' => 'Group discounts available for 10+ persons'],
                ['icon' => 'bi-backpack', 'text' => 'Bring comfortable clothing and footwear for outdoor activities'],
                ['icon' => 'bi-cup-straw', 'text' => 'Food and beverages available for purchase on site']
            ] as $info)
            <div class="flex items-start bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-all duration-300">
                <i class="bi {{ $info['icon'] }} text-xl mr-4 mt-1 flex-shrink-0"></i>
                <span class="text-sm md:text-base leading-relaxed">{{ $info['text'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>