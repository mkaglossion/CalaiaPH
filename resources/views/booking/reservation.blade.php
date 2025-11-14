<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>CalaiaPH - Booking</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body class="bg-gray-50">
  {{-- Header --}}
  <x-header />

  {{-- Booking Form --}}
  <main class="py-8 md:py-12">
    <div class="max-w-4xl mx-auto px-4">
      <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
        <h1 class="text-2xl md:text-3xl font-bold text-center mb-8 text-[#8f5d54]">Book Your Experience</h1>

        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
          <h2 class="text-lg font-semibold mb-2">Selected Package:</h2>
          <p class="text-gray-700"><strong>Type:</strong> {{ ucfirst($type) }}</p>
          <p class="text-gray-700"><strong>Package:</strong> {{ $package }}</p>
          <p class="text-gray-700"><strong>Price:</strong> ₱{{ number_format((float)$price) }}</p>
        </div>

        <form action="{{ route('booking.store') }}" method="POST" class="space-y-6">
          @csrf

          {{-- Personal Information --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
              <input type="text" id="first_name" name="first_name" required
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent">
            </div>
            <div>
              <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
              <input type="text" id="last_name" name="last_name" required
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input type="email" id="email" name="email" required
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent">
            </div>
            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
              <input type="tel" id="phone" name="phone" required
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent">
            </div>
          </div>

          {{-- Booking Details --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-2">Preferred Date</label>
              <input type="date" id="booking_date" name="booking_date" required
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent">
            </div>
            <div>
              <label for="guests" class="block text-sm font-medium text-gray-700 mb-2">Number of Guests</label>
              <input type="number" id="guests" name="guests" min="1" required
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent">
            </div>
          </div>

          {{-- Special Requests --}}
          <div>
            <label for="special_requests" class="block text-sm font-medium text-gray-700 mb-2">Special Requests (Optional)</label>
            <textarea id="special_requests" name="special_requests" rows="4"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent"
                      placeholder="Any special requirements or notes..."></textarea>
          </div>

          {{-- Hidden Fields --}}
          <input type="hidden" name="type" value="{{ $type }}">
          <input type="hidden" name="package" value="{{ $package }}">
          <input type="hidden" name="price" value="{{ $price }}">

          {{-- Submit Button --}}
          <div class="flex justify-center">
            <button type="submit" class="bg-[#8f5d54] text-white px-8 py-3 rounded-lg hover:bg-[#a0675a] transition-colors duration-300 font-semibold">
              Confirm Booking
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>

  {{-- Footer --}}
  <x-footer />

  <style>
  .font-gotham {
    font-family: 'Gotham', sans-serif;
  }
  .font-vila-zilka {
    font-family: 'Vila Zilka Serif', serif;
  }
  </style>
</body>
</html>