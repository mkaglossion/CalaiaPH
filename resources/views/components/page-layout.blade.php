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
    {{-- policy styles moved to resources/css/app.css --}}

    @stack('styles')
</head>

<body class="{{ $bodyClass }}">
    {{-- Force body to be interactive --}}
    <style>
        body { overflow: visible !important; position: relative !important; }
        [x-cloak] { display: none !important; }
        .fixed { position: fixed !important; }
    </style>
    
    {{-- Close any stuck modals on page load --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Force close all modals
            setTimeout(function() {
                try {
                    if (window.Alpine && Alpine.store('modals')) {
                        Alpine.store('modals').showLogin = false;
                        Alpine.store('modals').showSignup = false;
                        Alpine.store('modals').showCart = false;
                    }
                    // Remove any fixed overlays
                    document.querySelectorAll('.fixed.inset-0.bg-black').forEach(function(el) {
                        el.remove();
                    });
                } catch (e) {
                    console.log('Modal cleanup skipped');
                }
            }, 100);
        });
    </script>
    
    {{-- Flash Messages --}}
    <x-flash-message />

    {{-- Header --}}
    <x-header active="{{ $activeHeader }}" />

    {{-- Main Content --}}
    {{ $slot }}

    {{-- Footer --}}
    <x-footer />

    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('scripts')
</body>
</html>