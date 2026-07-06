{{-- resources/views/components/admin-layout.blade.php --}}
@props([
    'title' => 'CalaiaPH Admin',
    'description' => 'Administrative dashboard for CalaiaPH.',
    'keywords' => 'CalaiaPH admin, dashboard, management',
    'bodyClass' => 'bg-slate-100 text-gray-900',
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
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen">
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
