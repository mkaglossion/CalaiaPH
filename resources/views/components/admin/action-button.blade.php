@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'as' => null,
    'icon' => null,
    'iconClass' => 'h-4 w-4',
])

@php
    $tag = $as ?? ($href ? 'a' : 'button');

    $baseClasses = match ($variant) {
        'secondary' => 'inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white text-gray-700 shadow-sm transition hover:bg-gray-100',
        'outline' => 'inline-flex items-center justify-center gap-2 rounded-xl border border-[#d8bcb7] bg-white text-[#8f5d54] shadow-sm transition hover:bg-[#fdf7f5]',
        'link' => 'inline-flex items-center gap-1 font-semibold text-[#8f5d54] transition hover:text-[#7a4d46] hover:underline',
        default => 'inline-flex items-center justify-center gap-2 rounded-xl bg-[#8f5d54] text-white shadow transition hover:bg-[#7a4d46] hover:shadow-lg',
    };
@endphp

<{{ $tag }}
    @if($tag === 'a' && $href) href="{{ $href }}" @endif
    @if($tag === 'button') type="{{ $type }}" @endif
    {{ $attributes->merge(['class' => $baseClasses]) }}
>
    @if($icon)
        <i data-lucide="{{ $icon }}" class="{{ $iconClass }}"></i>
    @endif

    {{ $slot }}
</{{ $tag }}>
