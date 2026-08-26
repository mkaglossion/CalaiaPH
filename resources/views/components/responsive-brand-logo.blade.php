@props([
    'settings' => [],
    'viewport' => 'responsive',
    'fallbackClass' => 'text-xl font-semibold tracking-[0.3em] text-white uppercase',
])

@php
    $resolvedSettings = array_merge(
        \App\Models\SystemSetting::defaults(),
        is_array($settings) ? $settings : []
    );
    $logoSource = $resolvedSettings['logo_url'] ?? null;
    $logoUrl = $logoSource
        ? \App\Helpers\CloudinaryHelper::logo($logoSource, \App\Models\SystemSetting::HEADER_LOGO_WIDTH_MAX)
        : null;
    $desktopWidth = \App\Models\SystemSetting::headerLogoWidth($resolvedSettings['header_logo_width'] ?? null);
    $mobileWidth = \App\Models\SystemSetting::mobileHeaderLogoWidth($resolvedSettings['mobile_header_logo_width'] ?? null);
    $logoClass = trim((string) $attributes->get('class', ''));
    $showDesktop = in_array($viewport, ['desktop', 'responsive'], true);
    $showMobile = in_array($viewport, ['mobile', 'responsive'], true);
@endphp

@if($logoUrl)
    @if($showDesktop)
        <img
            src="{{ $logoUrl }}"
            alt="{{ $resolvedSettings['app_name'] ?? 'Calaia PH' }}"
            data-header-logo
            data-logo-viewport="desktop"
            data-logo-width="{{ $desktopWidth }}"
            class="{{ $viewport === 'responsive' ? 'hidden md:block ' : '' }}{{ $logoClass }}"
            style="width: {{ $desktopWidth }}px; max-width: 100%; height: auto;"
        >
    @endif

    @if($showMobile)
        <img
            src="{{ $logoUrl }}"
            alt="{{ $resolvedSettings['app_name'] ?? 'Calaia PH' }}"
            data-header-logo
            data-logo-viewport="mobile"
            data-logo-width="{{ $mobileWidth }}"
            class="{{ $viewport === 'responsive' ? 'block md:hidden ' : '' }}{{ $logoClass }}"
            style="width: min({{ $mobileWidth }}px, 100%); height: auto;"
        >
    @endif
@else
    <span class="{{ $fallbackClass }}">
        {{ $resolvedSettings['app_name'] ?? 'Calaia PH' }}
    </span>
@endif
