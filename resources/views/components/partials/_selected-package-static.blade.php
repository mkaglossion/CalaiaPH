{{-- Package Item - Static (PHP) --}}
<div class="bg-blue-50 rounded-lg border-2 border-blue-200 p-4">
    <div class="flex items-start justify-between mb-3">
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <i class="bi bi-box2 text-blue-600"></i>
                <p class="font-bold text-blue-900">{{ $selectedFood['name'] }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="font-bold text-[#8f5d54]">₱{{ number_format($itemTotal, 0) }}</p>
        </div>
    </div>

    <div class="border-t border-blue-200 pt-3">
        <p class="text-xs font-semibold text-blue-900 uppercase tracking-wide mb-2">Includes:</p>
        <div class="space-y-1">
            @foreach($includedFoods as $item)
                <div class="text-blue-800 text-xs">
                    <span class="font-semibold">{{ $item['quantity'] }}x</span>
                    <span>{{ $item['name'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="border-t border-blue-200 pt-3 mt-3 flex justify-between items-center">
        <span class="text-sm font-semibold text-gray-700">Subtotal:</span>
        <span class="font-bold text-[#8f5d54]">₱{{ number_format($itemTotal, 0) }}</span>
    </div>
</div>
