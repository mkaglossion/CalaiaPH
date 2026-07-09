{{-- Package Item - Static (PHP) --}}
<div class="bg-white rounded-lg border border-gray-200 p-4 space-y-3">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <i class="bi bi-box2 text-gray-600"></i>
                <p class="font-bold text-gray-900">{{ $selectedFood['name'] }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="font-bold text-[#8f5d54]">₱{{ number_format($itemTotal, 0) }}</p>
        </div>
    </div>

    <div class="border-t border-gray-200 pt-3">
        @if(!empty($selectedChoices ?? []))
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Choices:</p>
            <div class="space-y-1 mb-3">
                @foreach($selectedChoices as $choice)
                    <div class="text-gray-700 text-xs">
                        <span class="font-semibold text-gray-600">{{ $choice['section_title'] ?? 'Choice' }}:</span>
                        <span>{{ $choice['food_name'] ?? 'Selected item' }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        @if(!empty($includedFoods))
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Includes:</p>
            <div class="space-y-1">
                @foreach($includedFoods as $item)
                    <div class="text-gray-700 text-xs">
                        <span class="font-semibold text-gray-600">{{ $item['quantity'] }}x</span>
                        <span>{{ $item['name'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($quantity > 1)
    <div class="border-t border-gray-200 pt-3 text-xs text-gray-600">
        <span>Quantity: <span class="font-semibold">{{ $quantity }}</span></span>
    </div>
    @endif
</div>
