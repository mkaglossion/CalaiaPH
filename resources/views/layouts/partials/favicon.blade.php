<!-- resources/views/layouts/partials/favicon.blade.php -->
@php
    $faviconSource = $systemSettings['favicon_url'] ?? ($systemSettings['logo_url'] ?? null);
    $faviconUrl = $faviconSource
        ? \App\Helpers\CloudinaryHelper::thumbnail($faviconSource, 96)
        : null;
@endphp

@if($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
@endif
