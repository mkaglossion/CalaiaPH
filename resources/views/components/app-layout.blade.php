{{-- resources/views/components/app-layout.blade.php --}}
@props([
    'title' => 'CalaiaPH',
    'description' => 'Welcome to CalaiaPH, your ultimate destination for farm stays, day tours, and outdoor adventures.',
    'keywords' => 'farm stay, day tours, outdoor activities, nature retreat, CalaiaPH, Philippines farm experience',
    'bodyClass' => 'bg-[#f5efe7]',
    'activeHeader' => 'home'
])

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="{{ $keywords }}">
    <meta name="author" content="CalaiaPH">
    @include('layouts.partials.favicon')
    @vite(['resources/js/app.js'])
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

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
    


    {{-- Header --}}
    <x-header active="{{ $activeHeader }}" />

    {{-- Main Content --}}
    {{ $slot }}

    {{-- Footer --}}
    <x-footer />

    {{-- Alpine is bundled and started once by resources/js/app.js. --}}
    <script>
    // Fix for flushLogs fetch error - aggressive override
    (function() {
      // Override flushLogs before any other scripts can define it
      Object.defineProperty(window, 'flushLogs', {
        value: function() {
          console.log('flushLogs called - disabled to prevent fetch errors');
          return Promise.resolve();
        },
        writable: false,
        enumerable: true,
        configurable: true
      });

      // Also override any scheduleFlush function that might be calling flushLogs
      Object.defineProperty(window, 'scheduleFlush', {
        value: function() {
          console.log('scheduleFlush called - disabled');
        },
        writable: false,
        enumerable: true,
        configurable: true
      });
    })();
    </script>
</body>
</html>
