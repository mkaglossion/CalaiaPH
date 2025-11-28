<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'CalaiaPH - ' . ucfirst(request()->segment(2) ?? 'Booking'))</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

  @stack('styles')
</head>

<body class="@yield('body-class', 'bg-gray-50 font-sans antialiased min-h-screen')" @yield('body-attributes')>
  {{-- Success/Error Messages --}}
  @include('partials.messages')

  {{-- Header --}}
  <x-header active="@yield('active-nav', 'booking')" />

  {{-- Progress Indicator --}}
  @hasSection('progress')
    @yield('progress')
  @endif

  {{-- Main Content --}}
  <main @yield('main-attributes')>
    @yield('content')
  </main>

  {{-- Footer --}}
  <x-footer />

  {{-- Toast Notifications --}}
  <x-toast />

  {{-- Common Styles --}}
  <style>
    .font-gotham {
      font-family: 'Gotham Book', sans-serif;
    }
  </style>

  {{-- Scripts --}}
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  @stack('scripts')
</body>
</html>