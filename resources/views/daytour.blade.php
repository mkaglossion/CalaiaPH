<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="Explore exciting day tours at CalaiaPH. From adventure packages to family fun, discover thrilling outdoor activities and nature experiences tailored for every group.">
  <meta name="keywords" content="day tours, adventure packages, outdoor activities, nature tours, CalaiaPH, family fun, group activities">
  <meta name="author" content="CalaiaPH">
  <title>CalaiaPH - Day Tour</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>

<body class="bg-white">
{{-- flash message --}}
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
        <x-flash-message type="success" :message="session('success')" />
      @endif
    </div>
  <x-header active="daytour" />
    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

  {{-- Day Tour Packages Section --}}
  <main class="py-12 md:py-16 lg:py-20" style="background-color: #f5efe7;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      {{-- Section Header --}}
      <!--<div class="text-center mb-12 md:mb-16">
        <h2 class="text-3xl font-bold text-[#8f5d54] mb-4 uppercase">
          Choose Your Adventure
        </h2>
        <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
          Discover the perfect day tour experience tailored to your interests. From thrilling adventures to peaceful nature escapes, we have something for everyone.
        </p>
      </div>-->

      {{-- Packages Section --}}
      {{-- Day Tour Cards Component (matches mock) --}}

      <div class="space-y-16">
        {{-- Packages Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
          @foreach($packages as $package)
            @php
              $title = is_array($package) ? $package['title'] : $package->name;
              $image = asset(is_array($package) ? $package['image'] : ($package->swiperImages->first()->image_path ?? 'images/default.jpg'));
              $features = is_array($package) ? $package['features'] : $package->activities->pluck('name');
              $price = is_array($package) ? 'PHP 1,500 per person' : $package->price;
              $details = is_array($package) ? $package["details"] : explode("\n", $package->description ?? "");
              $gallery = is_array($package) ? array_map(fn($img) => asset($img), $package["gallery"]) : $package->swiperImages->pluck("image_path")->map(fn($path) => asset($path))->toArray();
            @endphp

            <x-daytour-package-card
                :title="$title"
                :image="$image"
                :features="$features"
                :price="$price"
                :details="$details"
                :gallery="$gallery"
            />
          @endforeach
        </div>

        {{-- Information Section --}}
        <x-daytour-info-section />
      </div>
    </div>
  </main>

  {{-- Dynamic Details Modal --}}
  <x-package-modal id="detailsModal" :price="true" buttonText="Book Now" buttonAction="bookNow" />

  {{-- Floating Buttons --}}
  <x-floating-buttons />
  {{-- Footer --}}
  <x-footer />

  {{-- Scripts --}}
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    // Initialize modal functionality
    initializeModal();
  });

  let lastFocusedElement = null;

  function initializeModal() {
    document.querySelectorAll('button[data-title]').forEach(button => {
      button.addEventListener('click', function() {
        lastFocusedElement = this;
        const title = this.getAttribute('data-title');
        const details = JSON.parse(this.getAttribute('data-details'));
        const gallery = JSON.parse(this.getAttribute('data-gallery'));
        const price = this.getAttribute('data-price');
        openModaldetailsModal(title, details, gallery, price);
      });
    });
  }
  </script>
</body>
</html>