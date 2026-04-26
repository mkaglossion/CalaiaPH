{{-- Reusable checkmark badge component --}}
<div class="absolute top-2 left-2 z-10" x-show="{{ $condition }}" x-transition>
    <div class="w-6 h-6 bg-[#8f5d54] rounded-full flex items-center justify-center shadow">
        <i class="bi bi-check text-white text-sm font-bold"></i>
    </div>
</div>
