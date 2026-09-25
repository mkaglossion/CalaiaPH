@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
    'isAdmin' => false,
    'wide' => false,
])

@php
    $primary = $isAdmin ? '#8f5d54' : '#2563eb';
@endphp

<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-10 sm:py-14">
    <section @class([
        'w-full rounded-2xl bg-white p-6 text-gray-800 shadow-2xl sm:p-8',
        'max-w-3xl' => $wide,
        'max-w-md' => ! $wide,
    ]) aria-labelledby="auth-card-title">
        <div class="mb-6 text-center">
            @if($eyebrow)
                <p class="mb-2 text-xs font-bold uppercase tracking-[0.22em] text-gray-500">{{ $eyebrow }}</p>
            @endif

            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full" style="background-color: {{ $isAdmin ? '#f6eeee' : '#eff6ff' }}; color: {{ $primary }};">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M12 3 5.5 5.5v5.7c0 4.1 2.7 7.9 6.5 9.2 3.8-1.3 6.5-5.1 6.5-9.2V5.5L12 3Z" />
                    <path d="m9.5 12 1.8 1.8 3.6-4" />
                </svg>
            </div>

            <h1 id="auth-card-title" class="text-2xl font-semibold sm:text-3xl" style="color: {{ $primary }};">{{ $title }}</h1>

            @if($subtitle)
                <p class="mt-3 text-sm leading-6 text-gray-600">{{ $subtitle }}</p>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{ $slot }}

        <div class="mt-6 flex items-center justify-center gap-2 text-xs font-medium text-gray-500">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M12 3 5.5 5.5v5.7c0 4.1 2.7 7.9 6.5 9.2 3.8-1.3 6.5-5.1 6.5-9.2V5.5L12 3Z" />
                <path d="M9.5 12.2 11.3 14l3.5-4" />
            </svg>
            <span>Protected by Two-Factor Authentication</span>
        </div>
    </section>
</div>
