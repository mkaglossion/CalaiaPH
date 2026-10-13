{{-- resources/views/components/page-layout.blade.php --}}
@props([
    'title' => 'CalaiaPH',
    'description' => 'Welcome to CalaiaPH, your ultimate destination for farm stays, day tours, and outdoor adventures.',
    'keywords' => 'farm stay, day tours, outdoor activities, nature retreat, CalaiaPH, Philippines farm experience',
    'bodyClass' => '',
    'activeHeader' => 'home',
    'includeStaycationScript' => false,
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
    
    {{-- Vite handles all app CSS/JS. Do not hardcode asset links. --}}
    @vite(['resources/js/app.js'])
    @if($includeStaycationScript)
        @vite(['resources/js/booking/staycation.js'])
    @endif
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    {{-- policy styles moved to resources/css/app.css --}}

    @stack('styles')

    <noscript>
        <style>
            body { opacity: 1 !important; }
            .no-js-warning { display: block !important; }
        </style>
        <div class="no-js-warning" style="background: #ffefc1; color: #a75e3e; padding: 1em; text-align: center; font-weight: bold;">
            This site requires JavaScript for full functionality. Please enable JavaScript in your browser.
        </div>
    </noscript>
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



    {{-- Alpine is bundled and started once by resources/js/app.js. --}}
    @stack('scripts')

    <script>
    // Fix for flushLogs fetch error - provide safe defaults
    (function() {
      // Provide safe defaults if not already defined
      if (typeof window.flushLogs === 'undefined') {
        window.flushLogs = function() {
          console.log('flushLogs called');
          return Promise.resolve();
        };
      }

      if (typeof window.scheduleFlush === 'undefined') {
        window.scheduleFlush = function() {
          console.log('scheduleFlush called');
        };
      }
    })();

    // Fallback: warn if Vite JS fails to load (e.g., broken build, bad cache)
    window.addEventListener('DOMContentLoaded', function() {
        var viteScript = document.querySelector('script[src*="app"]');
        if (!viteScript) {
            var warn = document.createElement('div');
            warn.style.background = '#ffeaea';
            warn.style.color = '#a75e3e';
            warn.style.padding = '1em';
            warn.style.textAlign = 'center';
            warn.style.fontWeight = 'bold';
            warn.innerText = 'Warning: Site assets failed to load. Try clearing your browser cache or contact support.';
            document.body.prepend(warn);
        }
    });
    </script>
</body>
</html>
