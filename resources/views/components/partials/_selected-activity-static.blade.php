{{-- Regular Activity Item - Static (PHP) --}}
<div class="bg-white rounded-lg border border-gray-200 p-3">
    <div class="flex justify-between items-start mb-1">
        <span class="font-semibold text-gray-900">{{ $selectedActivity['name'] }}</span>
        <span class="font-bold text-[#8f5d54]">₱{{ number_format($itemTotal, 0) }}</span>
    </div>
    <div class="text-sm text-gray-600">
        <div>Guests: <span class="font-semibold">{{ $guests }}</span> × ₱{{ number_format($selectedActivity['price'] ?? 0, 0) }}</div>
        <div class="text-xs text-gray-500 capitalize">{{ $selectedActivity['category'] ?? 'Activity' }}</div>
    </div>
</div>
