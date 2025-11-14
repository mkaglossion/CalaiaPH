<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CalaiaPH - Staycation</title>
    @vite(['resources/css/app.css'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Pannellum CSS (VR) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css" />
  </head>
  <body class="bg-[#f5efe7]">
    <!-- flash message -->
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
        <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
          {{ session('success') }}
        </div>
      @endif
    </div>

    <x-header active="staycation" />

    <!-- ================= Hero Slider ================= -->
    <div class="swiper heroSwiper w-full h-64 sm:h-80 md:h-96 lg:h-[500px] xl:h-[600px]">
      <div class="swiper-wrapper">
        @if(count($swiperImages) > 0)
          @php $texts_array = array_values($texts); $labels_array = array_keys($texts); @endphp
          @foreach($swiperImages as $index => $image)
          <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
            <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->title ?? 'Slide Image' }}" class="w-full h-full object-cover object-center" loading="lazy">
            <div class="absolute inset-0 bg-black/40 z-10"></div>
            <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
              <div class="text-center px-4">
                @if(isset($texts_array[$index]))
                  <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4 uppercase" style="font-family:gotham; font-size:3.5rem;">{{ ucfirst($labels_array[$index]) }}</h2>
                  <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase" style="font-family:'Vila Zilka Serif', serif; font-style:italic; font-size:1.2rem;">{{ $texts_array[$index] }}</p>
                @endif
              </div>
            </div>
          </div>
          @endforeach
        @else
          <div class="swiper-slide relative flex items-center justify-center bg-gray-200 overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center z-20 text-gray-600">
              <div class="text-center px-4">
                <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4 uppercase"
                    style="font-family: gotham; font-size: 3.5rem;">
                  No Image Available
                </h2>
                <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase"
                  style="font-family: 'Vilaka Modern Serif', serif; font-style: italic; font-size: 1.2rem;">
                  Images will be added soon
                </p>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>

    <!-- ================= Main ================= -->
    <main id="main" class="" x-data="staycationPage()" x-on:open-staycation-modal.window="openModal($event.detail)">
      <!-- Cards patterned to provided design -->
      <section id="glamping" class="container mx-auto max-w-6xl px-4 py-10">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">

          <!-- Card: Glamping Tent -->
          <article class="relative mx-auto w-full max-w-md rounded-[22px] bg-white p-5" style="box-shadow:0 10px 16px rgba(0,0,0,0.08);">
            <div class="rounded-xl bg-white overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <!-- image frame placeholder -->
              <img src="{{ asset('assets/images/carousel/Glamping-1.jpg') }}" alt="Glamping Tent" class="h-56 sm:h-64 w-full object-cover">
            </div>
            <div class="px-4 py-7 text-center">
              <h3 class="mb-4 text-3xl font-extrabold uppercase tracking-wide" style="color:#97655A;">Glamping Tent</h3>
              <div class="mx-auto my-4 h-px w-full" style="background:#dce8d6;"></div>
              <ul class="space-y-2 text-[12px] uppercase tracking-widest" style="color:#8f6a60;">
                <li>LUXURY TENT SETUP</li>
                <li>GOURMET LUNCH SERVICE</li>
                <li>PRIVATE ATV EXPERIENCE</li>
                <li>GUIDED NATURE TOUR</li>
                <li>CUSTOM TEAM BUILDING</li>
              </ul>
              <div class="mt-7">
                <button type="button" @click="openModal('glamping')" class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md" style="background:#97655A;color:#ffffff;">LEARN MORE</button>
              </div>
            </div>
          </article>

          <!-- Card: Camping -->
          <article class="relative mx-auto w-full max-w-md rounded-[22px] bg-white p-5" style="box-shadow:0 10px 16px rgba(0,0,0,0.08);">
            <div class="rounded-xl ring-1 ring-black/5 shadow-sm bg-white overflow-hidden">
              <img src="{{ asset('assets/images/carousel/BYOT-2.jpg') }}" alt="Camping" class="h-56 sm:h-64 w-full object-cover">
            </div>
            <div class="px-4 py-7 text-center">
              <h3 class="mb-4 text-3xl font-extrabold uppercase tracking-wide" style="color:#97655A;">Camping</h3>
              <div class="mx-auto my-4 h-px w-full" style="background:#dce8d6;"></div>
              <ul class="space-y-2 text-[12px] uppercase tracking-widest" style="color:#8f6a60;">
                <li>TENT SETUP ASSISTANCE</li>
                <li>CAMPFIRE BUILDING</li>
                <li>STARGAZING SESSION</li>
                <li>BASIC NATURE WALK</li>
                <li>FARM EXPLORATION</li>
              </ul>
              <div class="mt-7">
                <button type="button" @click="openModal('camping')" class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md" style="background:#97655A;color:#ffffff;">LEARN MORE</button>
              </div>
            </div>
          </article>

        </div>
      </section>

      <!-- ================= Glamping Options Modal ================= -->
      <div x-show="glampingOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="closeGlampingModal()" @click.self="closeGlampingModal()" style="display:none;">
        <div class="w-full max-w-6xl rounded-2xl bg-white shadow-xl overflow-auto max-h-[90vh]">
          <div class="flex items-center justify-between px-6 py-4 border-b" style="border-color: rgba(128,128,128,0.3);">
            <h3 class="text-2xl font-bold" style="color:#97655A"><i class="bi bi-stars me-2"></i>Glamping Options</h3>
            <button class="p-2 rounded hover:bg-gray-100" @click="closeGlampingModal()"><i class="bi bi-x-lg"></i></button>
          </div>

          <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Standard -->
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#6b7280;color:white"><h4 class="font-semibold flex items-center gap-2"><i class="bi bi-house-fill"></i> Standard</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/staycation.png') }}" class="w-full h-64 object-cover" alt="Standard Glamping 1"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/staycation.png') }}" class="w-full h-64 object-cover" alt="Standard Glamping 2"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/Glamping-1.jpg') }}" class="w-full h-64 object-cover" alt="Standard Glamping 3"></div>
                  </div>
                  <button type="button" aria-label="Previous slide" class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-prev>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide" class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-next>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                </div>
                <div class="flex gap-2 justify-center py-3" role="tablist" aria-label="Slides" data-custom-dots></div>
                <div class="flex items-center justify-between">
                  <p class="text-xl font-bold" style="color:#16a34a">Starts at ₱1,200/night</p>
                  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#e5e7eb;color:#374151">Standard</span>
                </div>
                <h6 class="font-semibold">Features</h6>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                  <li class="px-3 py-2 bg-gray-50 rounded">Shared bathroom facilities</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Double bed</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Electricity and heating</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Scenic views</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Comfortable accommodations</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">28 m² • 4 single beds</li>
                </ul>
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#6b7280" @click="book('{{ url('/booking/staycation?type=staycation&package=Standard%20Glamping&price=1200') }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('glamping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

            <!-- Premium -->
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#3b82f6;color:white"><h4 class="font-semibold flex items-center gap-2"><i class="bi bi-star-fill"></i> Premium</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/staycation.png') }}" class="w-full h-64 object-cover" alt="Premium Glamping 1"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/staycation.png') }}" class="w-full h-64 object-cover" alt="Premium Glamping 2"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/Glamping-1.jpg') }}" class="w-full h-64 object-cover" alt="Premium Glamping 3"></div>
                  </div>
                  <button type="button" aria-label="Previous slide" class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-prev>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide" class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-next>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                </div>
                <div class="flex gap-2 justify-center py-3" role="tablist" aria-label="Slides" data-custom-dots></div>
                <div class="flex items-center justify-between">
                  <p class="text-xl font-bold" style="color:#16a34a">Starts at ₱5,500 - ₱6,500/night</p>
                  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#dbeafe;color:#1d4ed8">Exclusive</span>
                </div>
                <h6 class="font-semibold">Features</h6>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                  <li class="px-3 py-2 bg-gray-50 rounded">Towels &amp; care kits</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">A/C &amp; Wi-Fi ready</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Picnic table &amp; fire pit</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Coffee/tea &amp; water</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Good for 6 adults</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">28 m² • 4 single beds</li>
                </ul>
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#3b82f6" @click="book('{{ url('/booking/staycation?type=staycation&package=Premium%20Glamping&price=5500') }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('glamping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>
          </div>

          <div class="px-6 py-4 border-t flex justify-end" style="border-color: rgba(128,128,128,0.3);">
            <button type="button" @click="closeGlampingModal()" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">Close</button>
          </div>
        </div>
      </div>

      <!-- ================= Camping Options Modal ================= -->
      <div x-show="campingOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="closeCampingModal()" @click.self="closeCampingModal()" style="display:none;">
        <div class="w-full max-w-6xl rounded-2xl bg-white shadow-xl overflow-auto max-h-[90vh]">
          <div class="flex items-center justify-between px-4 md:px-6 py-4 border-b" style="border-color: rgba(128,128,128,0.3);">
            <h3 class="text-2xl font-bold" style="color:#97655A"><i class="bi bi-house me-2"></i>Camping Options</h3>
            <button class="p-2 rounded hover:bg-gray-100" @click="closeCampingModal()"><i class="bi bi-x-lg"></i></button>
          </div>

          <div class="p-4 md:p-6 grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
            <!-- Dome Tent -->
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#3b82f6;color:white"><h4 class="font-semibold flex items-center gap-2"><i class="bi bi-tent"></i>Dome Tent</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/Glamping-1.jpg') }}" class="w-full h-64 object-cover" alt="Dome 1"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/Glamping-2.jpg') }}" class="w-full h-64 object-cover" alt="Dome 2"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/staycation.png') }}" class="w-full h-64 object-cover" alt="Dome 3"></div>
                  </div>
                  <button type="button" aria-label="Previous slide" class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-prev>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide" class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-next>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                </div>
                <div class="flex gap-2 justify-center py-3" role="tablist" aria-label="Slides" data-custom-dots></div>
                <div class="flex items-center justify-between">
                  <p class="text-xl font-bold" style="color:#16a34a">Starts at ₱1,200/night</p>
                  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#dbeafe;color:#1d4ed8">Comfort</span>
                </div>
                <h6 class="font-semibold">Features</h6>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                  <li class="px-3 py-2 bg-gray-50 rounded">4-person capacity</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Sleeping mats included</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Access to shared bathroom</li>
                </ul>
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#3b82f6" @click="book('{{ url('/booking/staycation?type=camping&package=Dome%20Tent&price=1200') }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

            <!-- Bell Tent -->
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#6b7280;color:white"><h4 class="font-semibold flex items-center gap-2"><i class="bi bi-house-fill"></i>Bell Tent</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/staycation.png') }}" class="w-full h-64 object-cover" alt="Bell 1"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/slide2.jpg') }}" class="w-full h-64 object-cover" alt="Bell 2"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/Glamping-1.jpg') }}" class="w-full h-64 object-cover" alt="Bell 3"></div>
                  </div>
                  <button type="button" aria-label="Previous slide" class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-prev>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide" class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-next>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                </div>
                <div class="flex gap-2 justify-center py-3" role="tablist" aria-label="Slides" data-custom-dots></div>
                <div class="flex items-center justify-between">
                  <p class="text-xl font-bold" style="color:#16a34a">Starts at ₱1,800/night</p>
                  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#e5e7eb;color:#374151">Spacious</span>
                </div>
                <h6 class="font-semibold">Features</h6>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                  <li class="px-3 py-2 bg-gray-50 rounded">Up to 6 guests</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Luxury canvas tent</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Shared bathroom</li>
                </ul>
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#16a34a" @click="book('{{ url('/booking/staycation?type=camping&package=Bell%20Tent&price=1800') }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

            <!-- BYOT -->
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#22c55e;color:white"><h4 class="font-semibold flex items-center gap-2"><i class="bi bi-backpack"></i>BYOT</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/BYOT-2.jpg') }}" class="w-full h-64 object-cover" alt="BYOT 1"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/slide2.jpg') }}" class="w-full h-64 object-cover" alt="BYOT 2"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/staycation.png') }}" class="w-full h-64 object-cover" alt="BYOT 3"></div>
                  </div>
                  <button type="button" aria-label="Previous slide" class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-prev>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide" class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-next>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                </div>
                <div class="flex gap-2 justify-center py-3" role="tablist" aria-label="Slides" data-custom-dots></div>
                <div class="flex items-center justify-between">
                  <p class="text-xl font-bold" style="color:#16a34a">Starts at ₱800/night</p>
                  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#dcfce7;color:#166534">Budget</span>
                </div>
                <h6 class="font-semibold">Features</h6>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                  <li class="px-3 py-2 bg-gray-50 rounded">Bring your own tent</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Spacious camping spot</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Access to shared bathroom</li>
                </ul>
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#6b7280" @click="book('{{ url('/booking/staycation?type=camping&package=BYOT&price=800') }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

            <!-- Car Camping -->
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#f59e0b;color:white"><h4 class="font-semibold flex items-center gap-2"><i class="bi bi-car-front-fill"></i>Car Camping</h4></div>
              <div class="p-4 space-y-4">
                <div class="swiper relative" data-swiper="modal">
                  <div class="swiper-wrapper">
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/Car-camping3.jpg') }}" class="w-full h-64 object-cover" alt="Car 1"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/slide2.jpg') }}" class="w-full h-64 object-cover" alt="Car 2"></div>
                    <div class="swiper-slide"><img src="{{ asset('assets/images/carousel/BYOT-2.jpg') }}" class="w-full h-64 object-cover" alt="Car 3"></div>
                  </div>
                  <button type="button" aria-label="Previous slide" class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-prev>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide" class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-black/60 text-white shadow" data-custom-next>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                </div>
                <div class="flex gap-2 justify-center py-3" role="tablist" aria-label="Slides" data-custom-dots></div>
                <div class="flex items-center justify-between">
                  <p class="text-xl font-bold" style="color:#16a34a">Starts at ₱1,500/night</p>
                  <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#fef3c7;color:#7c2d12">Convenient</span>
                </div>
                <h6 class="font-semibold">Features</h6>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                  <li class="px-3 py-2 bg-gray-50 rounded">Park next to your campsite</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Electrical hookup available</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Access to shared bathroom</li>
                </ul>
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#f59e0b" @click="book('{{ url('/booking/staycation?type=camping&package=Car%20Camping&price=1500') }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
              </div>
            </div>

          </div>
          <div class="px-4 md:px-6 py-4 border-t flex justify-end" style="border-color: rgba(128,128,128,0.3);">
            <button type="button" @click="closeCampingModal()" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">Close</button>
          </div>
        </div>
      </div>

      <!-- ================= VR Preview (Pannellum) ================= -->
      <div x-show="vrOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/80" @keydown.escape.window="closeVR()" @click.self="closeVR()" style="display:none;">
        <div class="relative w-full max-w-[1200px] mx-4 bg-black rounded-2xl shadow-2xl overflow-hidden">
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4" style="background:rgba(255,255,255,0.05)">
            <h3 class="text-white font-semibold"><i class="bi bi-eye me-2"></i>Virtual Reality Preview</h3>
            <button class="p-2 rounded hover:bg-white/10 text-white" @click="closeVR()"><i class="bi bi-x-lg"></i></button>
          </div>

          <!-- Viewer -->
          <div id="vr-media-container" class="relative w-full h-[65vh]"></div>

          <!-- Controls Bar -->
          <div class="absolute left-0 right-0 bottom-3 flex items-center justify-center pointer-events-none">
            <div class="flex items-center gap-3 px-4 py-2 rounded-full shadow-lg pointer-events-auto" style="background:rgba(0,0,0,.7)">
              <button class="px-3 py-2 rounded-md text-white" @click.stop="vrPrev()"><i class="bi bi-chevron-left"></i></button>
              <span id="vr-counter" class="px-3 py-2 rounded-md text-white/90 font-semibold">1 / 3</span>
              <button class="px-3 py-2 rounded-md text-white" @click.stop="vrNext()"><i class="bi bi-chevron-right"></i></button>
            </div>
          </div>

          <!-- Corners: left -->
          <div class="absolute left-3 bottom-3 flex gap-2">
            <button class="w-9 h-9 flex items-center justify-center rounded-md text-white/90 bg-black/60" title="Fullscreen" @click.stop="vrFullscreen()">
              <i class="bi bi-arrows-fullscreen"></i>
            </button>
            <button class="w-9 h-9 flex items-center justify-center rounded-md text-white/90 bg-black/60" title="Information" @click.stop="toggleVRInfo()">
              <i class="bi bi-info-circle"></i>
            </button>
          </div>
          <!-- Corners: right -->
          <div class="absolute right-3 bottom-3 flex gap-2">
            <button class="w-9 h-9 flex items-center justify-center rounded-md text-white/90 bg-black/60" title="Share" @click.stop="shareVR()">
              <i class="bi bi-share"></i>
            </button>
            <button class="w-9 h-9 flex items-center justify-center rounded-md text-white/90 bg-black/60" title="Close" @click.stop="closeVR()">
              <i class="bi bi-x"></i>
            </button>
          </div>

          <!-- Info overlay -->
          <div id="vr-info-overlay" class="absolute top-4 left-4 max-w-sm text-white rounded-md p-4" style="background:rgba(0,0,0,.8); display:none;">
            <div>
              <h6 id="vr-info-title" class="font-semibold mb-1">Title</h6>
              <p id="vr-info-description" class="text-sm text-white/80">Description</p>
              <div class="mt-2 flex gap-2">
                <span class="text-xs px-2 py-1 rounded bg-blue-600">360° View</span>
                <span class="text-xs px-2 py-1 rounded bg-green-600">HD Quality</span>
                <span class="text-xs px-2 py-1 rounded bg-cyan-600">Interactive</span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </main>

    {{-- Floating Buttons --}}
    <x-floating-buttons />
    <x-footer />

    <!-- ================= Scripts ================= -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Pannellum JS (VR) -->
    <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>

    <script>
      // ---------- Alpine component ----------
      function staycationPage(){
        // VR data (images can be equirectangular 360° or placeholders)
        const GLAMPING_VR = [
          {
            type: 'image',
            src: '{{ asset("assets/images/vr/glamping-tent-interior.jpg") }}',
            title: 'Glamping Tent Interior',
            description: 'Experience the comfort and luxury of our premium glamping tents with panoramic views of the farm.',
            hotspots: [
              { pitch: 0,  yaw: 0,   text: 'King Size Bed',     info: 'Comfortable king-size bed with premium linens' },
              { pitch: -10,yaw: 90,  text: 'Private Bathroom',  info: 'En-suite bathroom with hot shower' },
              { pitch: 5,  yaw: -45, text: 'Dining Area',       info: 'Private outdoor dining space' }
            ]
          },
          {
            type: 'image',
            src: '{{ asset("assets/images/vr/glamping-tent-exterior.jpg") }}',
            title: 'Glamping Tent Exterior',
            description: 'Beautiful exterior view of our luxury glamping tents surrounded by nature.',
            hotspots: [
              { pitch: -15, yaw: 135, text: 'Fire Pit',  info: 'Perfect spot for evening gatherings' },
              { pitch: 10,  yaw: 45,  text: 'Garden',    info: 'Stunning views of our organic gardens' }
            ]
          },
          {
            type: 'image',
            src: '{{ asset("assets/images/vr/farm-view-360.jpg") }}',
            title: 'Farm Panorama',
            description: 'Breathtaking panoramic views of our sustainable farm from the glamping area.',
            hotspots: [
              { pitch: 5,  yaw: -45, text: 'Organic Garden', info: 'Our organic vegetable garden' },
              { pitch: -15,yaw: 135, text: 'Horse Stable',   info: 'Visit our friendly horses and ponies' }
            ]
          }
        ];

        const CAMPING_VR = [
          {
            type: 'image',
            src: '{{ asset("assets/images/vr/dome-tent-setup.jpg") }}',
            title: 'Dome Tent Camping',
            description: 'Comfortable dome tent setup in our scenic camping grounds.',
            hotspots: [
              { pitch: 0,  yaw: 0,   text: 'Tent Setup',    info: '4-person dome tent with sleeping mats' },
              { pitch: -10,yaw: 90,  text: 'Campfire Area', info: 'Shared campfire area for all campers' }
            ]
          },
          {
            type: 'image',
            src: '{{ asset("assets/images/vr/bell-tent-interior.jpg") }}',
            title: 'Bell Tent Interior',
            description: 'Spacious bell tent perfect for groups and families.',
            hotspots: [
              { pitch: 5,  yaw: -45, text: 'Sleeping Area', info: 'Up to 6 guests in luxury canvas' },
              { pitch: -15,yaw: 135, text: 'Storage',       info: 'Ample storage for camping gear' }
            ]
          },
          {
            type: 'image',
            src: '{{ asset("assets/images/vr/byot-site.jpg") }}',
            title: 'BYOT Camping Site',
            description: 'Bring your own tent and enjoy our spacious camping areas.',
            hotspots: [
              { pitch: 10, yaw: 45,  text: 'Pitch Area',    info: 'Large area for tent setup' },
              { pitch: 0,  yaw: 90,  text: 'Facilities',    info: 'Access to bathrooms and showers' }
            ]
          }
        ];

        let pannellumViewer = null;
        let vrItems = GLAMPING_VR;
        let vrIndex = 0;

        function createHotspots(hs){
          return (hs || []).map(h => ({
            pitch: h.pitch, yaw: h.yaw, type: 'info', text: h.text,
            clickHandlerFunc: () => alert(h.text + '\n\n' + (h.info || ''))
          }));
        }

        function loadVR(index){
          if (!window.pannellum) {
            console.warn('Pannellum not loaded');
            return;
          }
          const container = document.getElementById('vr-media-container');
          if (!container) {
            console.warn('VR container not found');
            return;
          }
          container.innerHTML = '';
          const item = vrItems[index];

          try {
            // build viewer
            pannellumViewer = pannellum.viewer(container, {
              type: 'equirectangular',
              panorama: item.src,
              autoLoad: true,
              compass: true,
              showControls: true,
              hfov: 100,
              hotSpots: createHotspots(item.hotspots)
            });

            // update info & counter
            document.getElementById('vr-counter').textContent = (index + 1) + ' / ' + vrItems.length;
            document.getElementById('vr-info-title').textContent = item.title || '';
            document.getElementById('vr-info-description').textContent = item.description || '';
          } catch (e) {
            console.error('Failed to load VR viewer:', e);
            container.innerHTML = '<div class="flex items-center justify-center h-full text-white">Failed to load VR preview</div>';
          }
        }

        function navigate(delta){
          const next = vrIndex + delta;
          if (next < 0 || next >= vrItems.length) return;
          vrIndex = next;
          loadVR(vrIndex);
        }

        return {
          // modals
          glampingOpen: false,
          campingOpen: false,

          // VR state
          vrOpen: false,
          lastFocusedElement: null,
          openModal(which){
            if(which==='glamping') this.glampingOpen = true; else this.campingOpen = true;
            document.body.style.overflow = 'hidden';
            this.lastFocusedElement = document.activeElement;
            setTimeout(initModalSwipers, 50);
          },
          closeGlampingModal(){
            this.glampingOpen = false;
            document.body.style.overflow = '';
            document.querySelectorAll(".swiper[data-swiper='modal']").forEach(el => {
              if (el.swiper) {
                el.swiper.destroy();
                el.swiper = null;
              }
            });
            if (this.lastFocusedElement) {
              this.lastFocusedElement.focus();
            }
          },
          closeCampingModal(){
            this.campingOpen = false;
            document.body.style.overflow = '';
            document.querySelectorAll(".swiper[data-swiper='modal']").forEach(el => {
              if (el.swiper) {
                el.swiper.destroy();
                el.swiper = null;
              }
            });
            if (this.lastFocusedElement) {
              this.lastFocusedElement.focus();
            }
          },
          book(url){ window.location.href = url; },

          // VR API exposed to template
          openVR(type){
            vrItems = (type === 'camping') ? CAMPING_VR : GLAMPING_VR;
            vrIndex = 0;
            this.vrOpen = true;
            document.body.style.overflow = 'hidden';
            this.lastFocusedElement = document.activeElement;
            // wait for panel to appear then render
            this.$nextTick(() => {
              loadVR(vrIndex);
              setTimeout(() => { if (pannellumViewer) pannellumViewer.resize(); }, 100);
            });
          },
          closeVR(){
            this.vrOpen = false;
            document.body.style.overflow = '';
            if (this.lastFocusedElement) {
              this.lastFocusedElement.focus();
            }
            if (pannellumViewer) { try { pannellumViewer.destroy(); } catch(e){} pannellumViewer = null; }
          },
          vrPrev(){ navigate(-1); },
          vrNext(){ navigate(1); },
          vrFullscreen(){
            const el = document.querySelector('[x-show="vrOpen"] > div');
            if (!document.fullscreenElement) { el.requestFullscreen?.(); } else { document.exitFullscreen?.(); }
          },
          toggleVRInfo(){
            const el = document.getElementById('vr-info-overlay');
            el.style.display = (el.style.display === 'none' || !el.style.display) ? 'block' : 'none';
          },
          shareVR(){
            const item = vrItems[vrIndex] || {};
            const data = { title: item.title || 'VR Preview', text: 'Check out this VR preview', url: window.location.href };
            if (navigator.share) { navigator.share(data); }
            else { navigator.clipboard?.writeText((data.title||'')+' - '+data.url); alert('VR preview link copied to clipboard.'); }
          },
        }
      }

      // ---------- Swiper setup ----------
      function initHero(){
        if(!window.Swiper) return;
        const hero = document.querySelector('.heroSwiper');
        if(!hero) return;
        try {
          new Swiper(hero, {
            loop: true,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            keyboard: { enabled: true },
            a11y: { prevSlideMessage: 'Previous slide', nextSlideMessage: 'Next slide' },
            autoplay: { delay: 5000, disableOnInteraction: false },
          });
        } catch(e){ console.warn('Hero swiper init failed', e); }
      }

      function initModalSwipers(){
        if(!window.Swiper) return;
        document.querySelectorAll(".swiper[data-swiper='modal']").forEach((el)=>{
          try{
            if(!el.swiper){
              const swiper = new Swiper(el, {
                slidesPerView: 1,
                loop: true,
                navigation: { nextEl: el.querySelector('[data-custom-next]'), prevEl: el.querySelector('[data-custom-prev]') },
              });
              // Custom dots
              const dotsEl = el.parentElement.querySelector('[data-custom-dots]');
              const numSlides = 3; // Each modal swiper has 3 slides
              function updateDots() {
                dotsEl.innerHTML = '';
                for(let i=0; i<numSlides; i++){
                  const b = document.createElement('button');
                  b.type = 'button';
                  b.className = 'h-2.5 rounded-full ' + (i === swiper.realIndex ? 'bg-[#a97464] w-4' : 'bg-[#c8a89f] w-2.5');
                  b.setAttribute('role','tab');
                  b.setAttribute('aria-label', `Go to slide ${i+1}`);
                  b.addEventListener('click', ()=> swiper.slideToLoop(i));
                  dotsEl.appendChild(b);
                }
              }
              updateDots();
              swiper.on('slideChange', updateDots);
            }
          }catch(e){ console.warn('Modal swiper init failed', e); }
        });
      }

      document.addEventListener('DOMContentLoaded', function(){
        if(!window.Swiper){ console.warn('Swiper not loaded – skipping init'); return; }
        initHero();
        initModalSwipers();
        document.querySelectorAll('a[href^="#"]').forEach(a => {
          a.addEventListener('click', (e) => {
            const id = a.getAttribute('href');
            if (!id || id === '#') return;
            const target = document.querySelector(id);
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          });
        });
      });
    </script>
  </body>
</html>

