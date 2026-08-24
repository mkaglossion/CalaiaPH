@php
    $badgePositionClass = $positionClass
        ?? ('top-3 ' . (($position ?? 'left') === 'right' ? 'right-3' : 'left-3'));
@endphp
{{-- Reusable checkmark badge component - only shows when item is selected --}}
<div class="absolute {{ $badgePositionClass }} z-20"
     x-show="{{ $condition }}" 
     x-transition
     style="display: none;">
    <div class="relative">
        <div class="w-8 h-8 border-2 border-white bg-[#8f5d54] rounded-full flex items-center justify-center shadow-md">
            <i class="bi bi-check text-white text-sm font-bold"></i>
        </div>
        {{-- Quantity badge removed per design feedback --}}
    </div>
</div>
