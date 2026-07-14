@props([
    'title',
    'description',
    'ariaLabel',
    'errorKey' => null,
])

@php
    $errorKey = $errorKey ?: null;
@endphp

<div class="mt-5 rounded-xl border border-[#ddd3cb] bg-white p-4">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-slate-900">{{ $title }} <span class="text-red-500">*</span></p>
            <p class="text-xs text-slate-500">{{ $description }}</p>
        </div>
        <button
            type="button"
            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50"
            data-signature-clear
        >
            Clear
        </button>
    </div>
    <div class="mt-4 rounded-lg border border-slate-300 bg-[#fffefa] p-2 shadow-inner">
        <canvas
            class="h-36 w-full touch-none rounded-md bg-white outline-none focus:ring-2 focus:ring-[#b77b6d] focus:ring-offset-2 sm:h-40"
            data-signature-canvas
            tabindex="0"
            aria-label="{{ $ariaLabel }}"
        ></canvas>
    </div>
    <div class="mt-2 flex items-center gap-2 text-xs font-medium text-slate-500" data-signature-status>
        <span class="h-2 w-2 rounded-full bg-slate-300" data-signature-status-dot></span>
        <span data-signature-status-label>Signature required</span>
    </div>
    @if($errorKey)
        @error($errorKey)
            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>
