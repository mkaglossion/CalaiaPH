{{-- Regular Food Item - Static (PHP) --}}
@php
    $food = $foods->get($selectedFood['id']);
@endphp
@if($food)
<div class="bg-white rounded-lg border border-gray-100 p-3">
    <div class="flex justify-between items-start mb-1">
        <span class="font-semibold text-gray-900">{{ $food->name }}</span>
        <span class="font-bold text-[#8f5d54]">₱{{ number_format($itemTotal, 0) }}</span>
    </div>
    <div class="text-sm text-gray-600">
        <div>Qty: <span class="font-semibold">{{ $quantity }}</span> × ₱{{ number_format($food->price, 0) }}</div>
        @if(!empty($selectedFood['notes']))
            <div class="text-xs text-gray-500 italic mt-1">{{ $selectedFood['notes'] }}</div>
        @endif
    </div>
</div>
@endif
