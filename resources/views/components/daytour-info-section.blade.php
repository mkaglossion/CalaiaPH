@props([
    'title' => 'Important Information',
    'subtitle' => 'Everything you need to know before your visit',
    'infoItems' => [
        ['icon' => 'bi-clock', 'text' => 'Operating hours: 8:00 AM - 5:00 PM daily'],
        ['icon' => 'bi-calendar-check', 'text' => 'Advance booking recommended for guaranteed slots'],
        ['icon' => 'bi-person', 'text' => 'Children under 3 years old enter for free'],
        ['icon' => 'bi-people', 'text' => 'Group discounts available for 10+ persons'],
        ['icon' => 'bi-person', 'text' => 'Wear comfy clothing and footwear for outdoor activities'],
        ['icon' => 'bi-cup-straw', 'text' => 'Food and beverages available for purchase on site'],
    ]
])

<div class="bg-linear-to-r from-[#6b9691] to-[#7ba08a] text-white rounded-3xl p-8 md:p-10 shadow-xl">
    <div class="text-center mb-8">
        <h4 class="text-2xl md:text-3xl font-bold mb-4 flex items-center justify-center" style="font-size: 25px;">
            <i class="bi bi-info-circle-fill mr-3 text-3xl"></i>
            {{ $title }}
        </h4>
        <p class="text-lg/7 opacity-90" style="font-size: 13px;">{{ $subtitle }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($infoItems as $info)
            <div class="flex items-start bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-all duration-300">
                <i class="bi {{ $info['icon'] }} text-xl mr-4 mt-1 shrink-0"></i>
                <span class="text-sm md:text-base leading-relaxed" style="font-size: 13px;">{{ $info['text'] }}</span>
            </div>
        @endforeach
    </div>
</div>