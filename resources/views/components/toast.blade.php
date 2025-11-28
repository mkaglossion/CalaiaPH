@props([
    'id' => 'toast',
    'position' => 'bottom-4 right-4',
    'bgColor' => 'bg-emerald-600',
    'textColor' => 'text-white'
])

{{-- Toast Notification --}}
<div id="{{ $id }}" class="toast fixed {{ $position }} flex items-center {{ $textColor }} {{ $bgColor }} border-0 rounded-lg shadow-lg px-4 py-3 hidden"
     role="alert" aria-live="assertive" aria-atomic="true">
    <div class="flex items-center w-full">
        <div class="toast-body text-sm flex-1"></div>
        <button type="button"
                class="ml-3 {{ $textColor }} text-xs font-semibold uppercase tracking-wide btn-close"
                data-bs-dismiss="toast"
                aria-label="Close">
            ✕
        </button>
    </div>
</div>