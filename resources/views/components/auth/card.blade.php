@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
    'isAdmin' => false,
    'wide' => false,
    'compact' => false,
])

@php
    $primary = $isAdmin ? '#8f5d54' : '#2563eb';
@endphp

<div @class([
    'flex items-center justify-center bg-gray-100',
    'h-full overflow-hidden px-3 py-2 sm:px-4 sm:py-3' => $compact,
    'min-h-screen px-4 py-10 sm:py-14' => ! $compact,
])>
    <section @class([
        'w-full rounded-2xl bg-white text-gray-800 shadow-2xl',
        'auth-card-compact max-h-full overflow-hidden p-4 sm:p-5' => $compact,
        'p-6 sm:p-8' => ! $compact,
        'max-w-3xl' => $wide,
        'max-w-md' => ! $wide,
    ]) aria-labelledby="auth-card-title">
        <div @class([
            'auth-card-header text-center',
            'mb-3' => $compact,
            'mb-6' => ! $compact,
        ])>
            @if($eyebrow)
                <p @class([
                    'text-xs font-bold uppercase tracking-normal text-gray-500',
                    'mb-1' => $compact,
                    'mb-2' => ! $compact,
                ])>{{ $eyebrow }}</p>
            @endif

            <div @class([
                'auth-card-icon mx-auto flex items-center justify-center rounded-full',
                'mb-2 h-10 w-10' => $compact,
                'mb-4 h-12 w-12' => ! $compact,
            ]) style="background-color: {{ $isAdmin ? '#f6eeee' : '#eff6ff' }}; color: {{ $primary }};">
                <svg @class(['h-5 w-5' => $compact, 'h-6 w-6' => ! $compact]) viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M12 3 5.5 5.5v5.7c0 4.1 2.7 7.9 6.5 9.2 3.8-1.3 6.5-5.1 6.5-9.2V5.5L12 3Z" />
                    <path d="m9.5 12 1.8 1.8 3.6-4" />
                </svg>
            </div>

            <h1 id="auth-card-title" @class([
                'font-semibold',
                'text-xl sm:text-2xl' => $compact,
                'text-2xl sm:text-3xl' => ! $compact,
            ]) style="color: {{ $primary }};">{{ $title }}</h1>

            @if($subtitle)
                <p @class([
                    'auth-card-subtitle text-sm text-gray-600',
                    'mt-1 leading-5' => $compact,
                    'mt-3 leading-6' => ! $compact,
                ])>{{ $subtitle }}</p>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{ $slot }}

        <div @class([
            'auth-card-footer flex items-center justify-center gap-2 text-xs font-medium text-gray-500',
            'mt-3' => $compact,
            'mt-6' => ! $compact,
        ])>
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M12 3 5.5 5.5v5.7c0 4.1 2.7 7.9 6.5 9.2 3.8-1.3 6.5-5.1 6.5-9.2V5.5L12 3Z" />
                <path d="M9.5 12.2 11.3 14l3.5-4" />
            </svg>
            <span>Protected by Two-Factor Authentication</span>
        </div>
    </section>
</div>
