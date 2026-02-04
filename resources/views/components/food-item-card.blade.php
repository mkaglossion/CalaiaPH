{{-- resources/views/components/food-item-card.blade.php --}}
<div {{ $attributes->merge(['class' => 'bg-white rounded-lg border border-gray-200 p-3 hover:shadow-md transition-shadow']) }}>
    {{-- Item Header --}}
    <div class="flex justify-between items-start gap-3 mb-2">
        <h5 class="font-semibold text-gray-900 flex-1">
            {{ $food->name }}
        </h5>
        <span class="font-bold text-[#8f5d54] whitespace-nowrap">
            ₱{{ number_format($itemTotal, 0) }}
        </span>
    </div>

    {{-- Item Details --}}
    <div class="space-y-1 text-sm text-gray-600">
        <div class="flex items-center gap-1">
            <span class="text-gray-700">Qty:</span>
            <span class="font-semibold text-gray-900">{{ $quantity }}</span>
            <span class="text-gray-400">×</span>
            <span class="text-gray-700">₱{{ number_format($food->price, 0) }}</span>
        </div>

        {{-- Notes (if any) --}}
        @if($notes)
            <div class="text-xs text-gray-500 italic pt-1 border-t border-gray-100 mt-2">
                📝 {{ $notes }}
            </div>
        @endif
    </div>
</div>
