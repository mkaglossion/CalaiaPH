@props([
    'for',
    'labelText' => null,
    'required' => false,
    'errorKey' => null,
    'help' => null,
    'labelClass' => 'mb-2 block text-sm font-medium text-slate-700',
    'errorClass' => 'mt-1 text-xs text-red-600',
])

@php
    $labelContent = trim((string) ($labelText ?? ''));
    $errorKey = $errorKey ?: $for;
@endphp

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    <label for="{{ $for }}" class="{{ $labelClass }}">
        @isset($label)
            {{ $label }}
        @else
            {{ $labelContent }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        @endisset
    </label>

    {{ $slot }}

    @if(!empty($help))
        <p class="mt-1 text-xs text-slate-400">{{ $help }}</p>
    @endif

    @error($errorKey)
        <p class="{{ $errorClass }}">{{ $message }}</p>
    @enderror
</div>
