<!doctype html>
<html lang="en">
  <head>
    @include('layouts.partials.favicon')
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Order Confirmation - CalaiaPH">
    <meta name="keywords" content="order confirmation, CalaiaPH">
    <meta name="author" content="CalaiaPH">
    @vite(['resources/css/app.css'])
    <title>Order Confirmation - CalaiaPH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  </head>
  <body class="bg-[#f5efe7]">
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
        <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
            {{ session('success') }}
        </div>
      @endif
    </div>

    <x-header active="" />

    <div class="container mx-auto px-4 py-8 text-center">
        <div class="max-w-md mx-auto">
            <i class="bi bi-check-circle-fill text-green-500 text-6xl mb-4"></i>
            <h1 class="text-3xl font-bold text-[#8f5d54] mb-4">Order Confirmed!</h1>
            <p class="text-gray-600 mb-6">Thank you for your order. We've sent you a receipt via email with all the booking details.</p>
            <a href="{{ url('/') }}" class="bg-[#8f5d54] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#8f5d54]/90 transition">
                Back to Home
            </a>
        </div>
    </div>

    {{-- Footer --}}
    <x-footer />

    {{-- Alpine (needed for modal) --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>