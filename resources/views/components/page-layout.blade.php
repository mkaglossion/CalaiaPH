{{-- resources/views/components/page-layout.blade.php --}}
@props([
    'title' => 'CalaiaPH',
    'description' => 'Welcome to CalaiaPH, your ultimate destination for farm stays, day tours, and outdoor adventures.',
    'keywords' => 'farm stay, day tours, outdoor activities, nature retreat, CalaiaPH, Philippines farm experience',
    'bodyClass' => '',
    'activeHeader' => 'home'
])

<!doctype html>
<html lang="en">
<head>
    @include('layouts.partials.favicon')
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="{{ $keywords }}">
    <meta name="author" content="CalaiaPH">
    @vite(['resources/css/app.css'])
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    @stack('styles')
</head>

<body class="{{ $bodyClass }}">
    {{-- Flash Messages --}}
    <x-flash-message />

    {{-- Header --}}
    <x-header active="{{ $activeHeader }}" />

    {{-- Main Content --}}
    {{ $slot }}

    {{-- Footer --}}
    <x-footer />

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('scripts')
</body>
</html>