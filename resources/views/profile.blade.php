<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
    <title>Profile - CalaiaPH</title>
  </head>
  <body class="bg-gray-100">
    <x-header active="profile" />

    <div class="flex flex-col items-center justify-center min-h-screen">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">User Profile</h1>
        @if (session('success'))
            <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded relative" role="alert">
                {{ session('success') }}
            </div>
        @endif
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <p class="text-gray-700">Welcome to your profile page!</p>
            <!-- Add profile content here -->
        </div>
    </div>

    <x-footer />

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>