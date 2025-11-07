{{-- Day Tour Cards Component --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
    @foreach($packages as $package)
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <img src="{{ asset($package['image']) }}"
             class="h-80 w-full object-cover rounded-t-2xl"
             alt="{{ $package['title'] }} package"
             loading="lazy">
        <div class="p-6">
            <h3 class="text-xl md:text-2xl font-bold text-center mb-4 text-[#8f5d54]">{{ $package['title'] }}</h3>
            <div class="text-2xl font-bold text-green-600 text-center mb-6">{{ $package['price'] }}</div>
            <ul class="space-y-3 mb-6">
                @foreach($package['features'] as $feature)
                <li class="flex items-center justify-center text-[#8f5d54]">
                    <i class="bi bi-check-circle mr-2"></i>{{ $feature }}
                </li>
                @endforeach
            </ul>
            <div class="flex justify-center">
                <button type="button"
                        class="border-2 border-[#8f5d54] text-[#8f5d54] px-8 py-3 rounded-2xl hover:bg-[#8f5d54] hover:text-white transition-colors duration-300"
                        data-title="{{ $package['title'] }}"
                        data-details="{{ json_encode($package['details']) }}"
                        data-gallery="{{ json_encode(array_map(function($img) { return asset($img); }, $package['gallery'])) }}">
                    LEARN MORE
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Information Alert -->
<div class="bg-[#6b9691] text-white rounded-2xl p-6 shadow-lg">
    <h4 class="text-xl font-bold mb-4 flex items-center">
        <i class="bi bi-lightbulb-fill mr-3"></i>Day Tour Information
    </h4>
    <ul class="space-y-2 pl-8">
        <li class="flex items-center">
            <i class="bi bi-circle-fill text-xs mr-2"></i>Operating hours: 8:00 AM - 5:00 PM daily
        </li>
        <li class="flex items-center">
            <i class="bi bi-circle-fill text-xs mr-2"></i>Advance booking recommended for guaranteed slots
        </li>
        <li class="flex items-center">
            <i class="bi bi-circle-fill text-xs mr-2"></i>Children under 3 years old enter for free
        </li>
        <li class="flex items-center">
            <i class="bi bi-circle-fill text-xs mr-2"></i>Group discounts available for 10+ persons
        </li>
        <li class="flex items-center">
            <i class="bi bi-circle-fill text-xs mr-2"></i>Bring comfortable clothing and footwear for outdoor activities
        </li>
        <li class="flex items-center">
            <i class="bi bi-circle-fill text-xs mr-2"></i>Food and beverages available for purchase on site
        </li>
    </ul>
</div>