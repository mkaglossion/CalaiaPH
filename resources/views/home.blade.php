<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
    <title>CalaiaPH</title>
  </head>
  <body class="bg-gray-100">
    <x-header />

    {{-- ✅ Toast Notification --}}
    <div 
      x-data="{ show: {{ session('success') ? 'true' : 'false' }} }"
      x-show="show"
      x-init="if(show){ setTimeout(() => show = false, 3000) }"
      x-transition.duration.500ms
      class="fixed top-5 right-5 z-50"
    >
      <div class="bg-green-500 text-white font-medium px-5 py-3 rounded-lg shadow-lg flex items-center space-x-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span>{{ session('success') }}</span>
      </div>
    </div>

    <div class="flex flex-col items-center justify-center min-h-screen">
      {{-- Your main page content here --}}
    </div>

    <x-footer />

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>
