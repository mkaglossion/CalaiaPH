<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CalaiaPH - Staycation</title>
    @vite(['resources/css/app.css'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    {{-- Pannellum CSS (VR) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css" />
  </head>
  <body class="bg-[#f5efe7]">
    {{-- Validation Errors --}}
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
        <x-flash-message type="success" :message="session('success')" />
      @endif
    </div>

    <x-header active="staycation" />

    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

    {{-- Main --}}
    <main id="main" class="" x-data="staycationPage()" x-on:open-staycation-modal.window="openModal($event.detail)">
      {{-- Cards patterned to provided design --}}
      <section id="glamping" class="container mx-auto max-w-6xl px-4 py-10">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">

          {{-- Card: Glamping Tent --}}
          <article class="relative mx-auto w-full max-w-md rounded-[22px] bg-white p-5 group hover:shadow-2xl transition-all duration-500 hover:-translate-y-2" style="box-shadow:0 10px 16px rgba(0,0,0,0.08);">
            <div class="rounded-xl bg-white overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              {{-- image frame placeholder --}}
              <img src="{{ asset('assets/images/carousel/Glamping-1.jpg') }}" alt="Glamping Tent" class="h-56 sm:h-64 w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]">
            </div>
            <div class="px-4 py-7 text-center">
              <h3 class="mb-4 font-extrabold uppercase tracking-wide" style="color:#97655A; font-size: 25px; font-family: 'Gotham Book', sans-serif;">Glamping Tent</h3>
              <div class="mx-auto my-4 h-px w-full" style="background:#dce8d6;"></div>
              <ul class="space-y-2 text-xs uppercase tracking-widest" style="color:#8f6a60; font-family: 'Gotham Book', sans-serif;">
                <li>LUXURY TENT SETUP</li>
                <li>GOURMET LUNCH SERVICE</li>
                <li>PRIVATE ATV EXPERIENCE</li>
                <li>GUIDED NATURE TOUR</li>
                <li>CUSTOM TEAM BUILDING</li>
              </ul>
              <div class="mt-7">
                <button type="button" @click="openModal('glamping')" class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md" style="background:#b0775c;color:#ffffff;" onmouseover="this.style.backgroundColor='#b0775c'" onmouseout="this.style.backgroundColor='#b0775c'">LEARN MORE</button>
              </div>
            </div>
          </article>

          {{-- Card: Camping --}}
          <article class="relative mx-auto w-full max-w-md rounded-[22px] bg-white p-5 group hover:shadow-2xl transition-all duration-500 hover:-translate-y-2" style="box-shadow:0 10px 16px rgba(0,0,0,0.08);">
            <div class="rounded-xl ring-1 ring-black/5 shadow-sm bg-white overflow-hidden">
              <img src="{{ asset('assets/images/carousel/BYOT-2.jpg') }}" alt="Camping" class="h-56 sm:h-64 w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]">
            </div>
            <div class="px-4 py-7 text-center">
              <h3 class="mb-4 font-extrabold uppercase tracking-wide" style="color:#97655A; font-size: 25px; font-family: 'Gotham Book', sans-serif;">Camping</h3>
              <div class="mx-auto my-4 h-px w-full" style="background:#dce8d6;"></div>
              <ul class="space-y-2 text-xs uppercase tracking-widest" style="color:#8f6a60; font-family: 'Gotham Book', sans-serif;">
                <li>TENT SETUP ASSISTANCE</li>
                <li>CAMPFIRE BUILDING</li>
                <li>STARGAZING SESSION</li>
                <li>BASIC NATURE WALK</li>
                <li>FARM EXPLORATION</li>
              </ul>
              <div class="mt-7">
                <button type="button" @click="openModal('camping')" class="inline-flex items-center justify-center rounded-full px-10 py-3 text-sm font-semibold uppercase tracking-wide shadow-md" style="background:#b0775c;color:#ffffff;" onmouseover="this.style.backgroundColor='#b0775c'" onmouseout="this.style.backgroundColor='#b0775c'">LEARN MORE</button>
              </div>
            </div>
          </article>

        </div>
      </section>

      {{-- Glamping Options Modal --}}
      <div x-show="glampingOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="closeGlampingModal()" @click.self="closeGlampingModal()" style="display:none;">
        <div class="w-full max-w-7xl rounded-2xl bg-white shadow-xl overflow-auto max-h-[90vh]">
          <div class="flex items-center justify-between px-6 py-4 border-b" style="border-color: rgba(128,128,128,0.3);">
            <h3 class="text-2xl font-bold" style="color:#97655A; font-family: 'Gotham Book', sans-serif;"><i class="bi bi-stars me-2"></i>Glamping Options</h3>
            <button class="p-2 rounded hover:bg-gray-100" @click="closeGlampingModal()"><i class="bi bi-x-lg"></i></button>
          </div>

          <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Standard Glamping --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#6b7280;color:white"><h4 class="text-lg md:text-xl font-semibold flex items-center gap-2" style="font-family: 'Gotham Book', sans-serif;"><i class="bi bi-house-fill"></i> Standard Glamping</h4></div>
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
                      <path d="M9 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                </div>
                <div class="flex gap-2 justify-center py-3" role="tablist" aria-label="Slides" data-custom-dots></div>
                <div class="flex items-center justify-between">
                   <p class="font-bold leading-tight" style="color:#7a7d52; font-size: 20px; font-family: 'Gotham Book', sans-serif;">Start at ₱4,800/night (Weekdays: Sun.-Thu.)<br>₱6,000/night (Weekends: Fri.-Sat.)</p>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#e5e7eb;color:#374151">Standard Glamping</span>
                </div>
                <h6 class="text-base md:text-lg font-semibold">Features</h6>
                <ul class="grid grid-cols-1 gap-2 text-xs" style="font-family: 'Gotham Book', sans-serif;">
                  <li class="px-3 py-2 bg-gray-50 rounded">with Private Toilet & Bath</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Air-conditioned 25 sqm tent good for 4 pax (max of 6; additional Php 400/pax/night for extra mattress)</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Assigned heated shower with soap, shampoo & toilet with bidet (approx. 10 meters from tent)</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Access to Wi-Fi</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Private picnic table and fire pit/grill</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Complimentary coffee/tea and drinking water in tent</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Bathroom towels, dental & vanity kit, sleeping eye masks</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Convenience outlet</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Pet-friendly*</li>
                </ul>
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#6b7280" @click="book('{{ url('/booking/staycation?type=staycation&package=Standard%20Glamping&price=4800') }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('glamping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
                <div class="mt-2">
                  <button type="button" class="w-full rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openGallery('glamping', 'standard')"><i class="bi bi-images me-1"></i>Photo Gallery</button>
                </div>
              </div>
            </div>

            {{-- Premium Glamping --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#00848d;color:white"><h4 class="text-lg md:text-xl font-semibold flex items-center gap-2" style="font-family: 'Gotham Book', sans-serif;"><i class="bi bi-star-fill"></i> Premium Glamping</h4></div>
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
                   <p class="font-bold leading-tight" style="color:#7a7d52; font-size: 20px; font-family: 'Gotham Book', sans-serif;">Starts at ₱7,200/night (Weekends: Fri.-Sat.)<br>₱5,760/night (Weekdays: Sun.-Thu.)</p>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#dbeafe;color:#1d4ed8">Premium Glamping</span>
                </div>
                <h6 class="text-base md:text-lg font-semibold">Features</h6>
                <ul class="grid grid-cols-1 gap-2 text-xs" style="font-family: 'Gotham Book', sans-serif;">
                  <li class="px-3 py-2 bg-gray-50 rounded">with Private Toilet & Bath</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Air-conditioned 25 sqm tent good for 4 pax (max of 6; additional Php 400/pax/night for extra mattress)</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Assigned heated shower with soap, shampoo & toilet with bidet (approx. 10 meters from tent)</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Access to Wi-Fi</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Private picnic table and fire pit/grill</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Complimentary coffee/tea and drinking water in tent</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Exclusive heated shower with soap, shampoo & toilet with bidet, located right beside the tent</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Access to Wi-Fi</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Pet-friendly*</li>
                </ul>
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#00848d" @click="book('{{ url('/booking/staycation?type=staycation&package=Premium%20Glamping&price=5500') }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('glamping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
                <div class="mt-2">
                  <button type="button" class="w-full rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openGallery('glamping', 'premium')"><i class="bi bi-images me-1"></i>Photo Gallery</button>
                </div>
              </div>
            </div>
          </div>

          <div class="px-6 py-4 border-t flex justify-end" style="border-color: rgba(128,128,128,0.3);">
            <button type="button" @click="closeGlampingModal()" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">Close</button>
          </div>
        </div>
      </div>

      {{-- Camping Options Modal --}}
      <div x-show="campingOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="closeCampingModal()" @click.self="closeCampingModal()" style="display:none;">
        <div class="w-full max-w-7xl rounded-2xl bg-white shadow-xl overflow-auto max-h-[90vh]">
          <div class="flex items-center justify-between px-4 md:px-6 py-4 border-b" style="border-color: rgba(128,128,128,0.3);">
            <h3 class="text-2xl font-bold" style="color:#97655A; font-family: 'Gotham Book', sans-serif;"><i class="bi bi-house me-2"></i>Camping Options</h3>
            <button class="p-2 rounded hover:bg-gray-100" @click="closeCampingModal()"><i class="bi bi-x-lg"></i></button>
          </div>

          <div class="p-4 md:p-6 grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
            {{-- Dome Tent --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#00848d;color:white"><h4 class="text-lg md:text-xl font-semibold flex items-center gap-2" style="font-family: 'Gotham Book', sans-serif;"><i class="bi bi-tent"></i>Dome Tent</h4></div>
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
                   <p class="font-bold" style="color:#7a7d52; font-size: 20px; font-family: 'Gotham Book', sans-serif;">Starts at ₱ 3,000/tent/night</p>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#dbeafe;color:#1d4ed8">Comfort</span>
                </div>
                <h6 class="text-base md:text-lg font-semibold">Features</h6>
                <ul class="grid grid-cols-1 gap-2 text-xs" style="font-family: 'Gotham Book', sans-serif;">
                  <li class="px-3 py-2 bg-gray-50 rounded">Fan-cooled, 25 sqm tent</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Spacious enough for up to 8 mattress sets (+Php 400 per set/night) or 12 sleeping bags</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Common toilet and shower</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Convenience outlets</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Access to Wi-Fi, camp amenities, and activities</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Pet-friendly*</li>
                </ul>
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#00848d" @click="book('{{ url('/booking/staycation?type=camping&package=Dome%20Tent&price=1200') }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
                <div class="mt-2">
                  <button type="button" class="w-full rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openGallery('camping', 'dome')"><i class="bi bi-images me-1"></i>Photo Gallery</button>
                </div>
              </div>
            </div>

            {{-- Bell Tent --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#7a7d52;color:white"><h4 class="text-base md:text-lg font-semibold flex items-center gap-2"><i class="bi bi-house-fill"></i>Bell Tent</h4></div>
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
                   <p class="font-bold" style="color:#7a7d52; font-size: 20px; font-family: 'Gotham Book', sans-serif;">Starts at ₱3,000/tent/night</p>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#e5e7eb;color:#374151">Spacious</span>
                </div>
                <h6 class="text-base md:text-lg font-semibold">Features</h6>
                <ul class="grid grid-cols-1 gap-2 text-xs" style="font-family: 'Gotham Book', sans-serif;">
                  <li class="px-3 py-2 bg-gray-50 rounded">Fan-cooled, 25 sqm tent</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Spacious enough for up to 8 mattress sets (+Php 400 per set/night) or 12 sleeping bags</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Common toilet and shower</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Convenience outlets</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Access to Wi-Fi, camp amenities, and activities</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Pet-friendly*</li>
                </ul>
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#7a7d52" @click="book('{{ url('/booking/staycation?type=camping&package=Bell%20Tent&price=1800') }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
                <div class="mt-2">
                  <button type="button" class="w-full rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openGallery('camping', 'bell')"><i class="bi bi-images me-1"></i>Photo Gallery</button>
                </div>
              </div>
            </div>

            {{-- BYOT --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#843c23;color:white"><h4 class="text-base md:text-lg font-semibold flex items-center gap-2"><i class="bi bi-backpack"></i>BYOT</h4></div>
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
                   <p class="font-bold" style="color:#7a7d52; font-size: 20px; font-family: 'Gotham Book', sans-serif;">Starts at ₱300/tent or camp space + ₱500/pax overnight fee</p>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#dcfce7;color:#166534">Budget</span>
                </div>
                <h6 class="text-lg md:text-xl font-semibold" style="font-family: 'Gotham Book', sans-serif;">Features</h6>
                <ul class="grid grid-cols-1 gap-2 text-xs" style="font-family: 'Gotham Book', sans-serif;">
                  <li class="px-3 py-2 bg-gray-50 rounded">Spacious area for your own tent or car camp setup</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Common toilet and shower</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Access to Wi-Fi, camp amenities, and activities</li>
                </ul>
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#843c23" @click="book('{{ url('/booking/staycation?type=camping&package=BYOT&price=800') }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
                <div class="mt-2">
                  <button type="button" class="w-full rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openGallery('camping', 'byot')"><i class="bi bi-images me-1"></i>Photo Gallery</button>
                </div>
              </div>
            </div>

            {{-- Car Camping --}}
            <div class="rounded-xl overflow-hidden" style="border: 1px solid rgba(128,128,128,0.5);">
              <div class="px-4 py-2" style="background:#db8636;color:white"><h4 class="text-base md:text-lg font-semibold flex items-center gap-2"><i class="bi bi-car-front-fill"></i>Car Camping</h4></div>
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
                   <p class="font-bold" style="color:#7a7d52; font-size: 20px; font-family: 'Gotham Book', sans-serif;">Starts at ₱300/tent or camp space + Php 500/pax overnight fee</p>
                   <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" style="background:#fef3c7;color:#7c2d12">Convenient</span>
                </div>
                <h6 class="text-xl md:text-2xl font-semibold" style="font-family: 'Gotham Book', sans-serif;">Features</h6>
                <ul class="grid grid-cols-1 gap-2 text-xs" style="font-family: 'Gotham Book', sans-serif;">
                  <li class="px-3 py-2 bg-gray-50 rounded">Spacious area for your own tent or car camp setup</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Common toilet and shower</li>
                  <li class="px-3 py-2 bg-gray-50 rounded">Access to Wi-Fi, camp amenities, and activities</li>
                </ul>
                <div class="flex gap-2">
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold text-white" style="background:#db8636" @click="book('{{ url('/booking/staycation?type=camping&package=Car%20Camping&price=1500') }}')">Book Now</button>
                  <button type="button" class="flex-1 rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openVR('camping')"><i class="bi bi-eye me-1"></i>VR Preview</button>
                </div>
                <div class="mt-2">
                  <button type="button" class="w-full rounded-lg px-4 py-2 font-semibold border" style="border-color:#0ea5e9;color:#0ea5e9" @click="openGallery('camping', 'car')"><i class="bi bi-images me-1"></i>Photo Gallery</button>
                </div>
              </div>
            </div>

          </div>
          <div class="px-4 md:px-6 py-4 border-t flex justify-end" style="border-color: rgba(128,128,128,0.3);">
            <button type="button" @click="closeCampingModal()" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">Close</button>
          </div>
        </div>
      </div>

      {{-- VR Preview (Pannellum) --}}
      <div x-show="vrOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/80" @keydown.escape.window="closeVR()" @click.self="closeVR()" style="display:none;">
        <div class="relative w-full max-w-300 mx-4 bg-black rounded-2xl shadow-2xl overflow-hidden">
          {{-- Header --}}
          <div class="flex items-center justify-between px-6 py-4" style="background:rgba(255,255,255,0.05)">
            <h3 class="text-white font-semibold"><i class="bi bi-eye me-2"></i>Virtual Reality Preview</h3>
            <button class="p-2 rounded hover:bg-white/10 text-white" @click="closeVR()"><i class="bi bi-x-lg"></i></button>
          </div>

          {{-- Viewer --}}
          <div id="vr-media-container" class="relative w-full h-[65vh]"></div>

          {{-- Controls Bar --}}
          <div class="absolute left-0 right-0 bottom-3 flex items-center justify-center pointer-events-none">
            <div class="flex items-center gap-3 px-4 py-2 rounded-full shadow-lg pointer-events-auto" style="background:rgba(0,0,0,.7)">
              <button class="px-3 py-2 rounded-md text-white" @click.stop="vrPrev()"><i class="bi bi-chevron-left"></i></button>
              <span id="vr-counter" class="px-3 py-2 rounded-md text-white/90 font-semibold">1 / 3</span>
              <button class="px-3 py-2 rounded-md text-white" @click.stop="vrNext()"><i class="bi bi-chevron-right"></i></button>
            </div>
          </div>

          {{-- Corners: left --}}
          <div class="absolute left-3 bottom-3 flex gap-2">
            <button class="w-9 h-9 flex items-center justify-center rounded-md text-white/90 bg-black/60" title="Fullscreen" @click.stop="vrFullscreen()">
              <i class="bi bi-arrows-fullscreen"></i>
            </button>
            <button class="w-9 h-9 flex items-center justify-center rounded-md text-white/90 bg-black/60" title="Information" @click.stop="toggleVRInfo()">
              <i class="bi bi-info-circle"></i>
            </button>
          </div>
          {{-- Corners: right --}}
          <div class="absolute right-3 bottom-3 flex gap-2">
            <button class="w-9 h-9 flex items-center justify-center rounded-md text-white/90 bg-black/60" title="Share" @click.stop="shareVR()">
              <i class="bi bi-share"></i>
            </button>
            <button class="w-9 h-9 flex items-center justify-center rounded-md text-white/90 bg-black/60" title="Close" @click.stop="closeVR()">
              <i class="bi bi-x"></i>
            </button>
          </div>

          {{-- Info overlay --}} 
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

      {{-- Photo Gallery Modal --}}
      <div x-show="galleryOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/80" @keydown.escape.window="closeGallery()" @click.self="closeGallery()" style="display:none;">
        <div class="relative w-full max-w-4xl mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden">
          {{-- Header --}}
          <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-semibold"><i class="bi bi-images me-2"></i>Photo Gallery</h3>
            <button class="p-2 rounded hover:bg-gray-100" @click="closeGallery()"><i class="bi bi-x-lg"></i></button>
          </div>

          {{-- Image Viewer --}}
          <div class="flex items-center justify-center h-96 bg-black">
            <img x-show="galleryImages.length > 0" :src="galleryImages[galleryCurrentIndex]" class="max-w-full max-h-full object-contain" :alt="'Gallery image ' + (galleryCurrentIndex + 1)">
            <div x-show="galleryImages.length === 0" class="text-white">No images available</div>
          </div>

          {{-- Navigation --}}
          <div class="flex items-center justify-center gap-4 p-4 border-t">
            <button class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" @click="galleryPrev()" :disabled="galleryCurrentIndex === 0"><i class="bi bi-chevron-left"></i> Prev</button>
            <span x-text="' ' + (galleryCurrentIndex + 1) + ' / ' + galleryImages.length"></span>
            <button class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" @click="galleryNext()" :disabled="galleryCurrentIndex === galleryImages.length - 1">Next <i class="bi bi-chevron-right"></i></button>
          </div>
        </div>
      </div>

    </main>

    {{-- Floating Buttons --}} 
    <x-floating-buttons />
    <x-footer />

    @php
    $vrData = [
        'glamping' => [
            'images' => [
                ['src' => asset('assets/images/carousel/Glamping-1.jpg'), 'title' => 'Glamping Tent Interior', 'desc' => 'Luxurious tent setup with all amenities'],
                ['src' => asset('assets/images/carousel/staycation.png'), 'title' => 'Glamping Area View', 'desc' => 'Beautiful surroundings of the glamping site'],
                ['src' => asset('assets/images/carousel/Glamping-1.jpg'), 'title' => 'Glamping Facilities', 'desc' => 'Access to premium facilities and services']
            ],
            'info' => ['title' => 'Glamping VR Experience', 'desc' => 'Immerse yourself in the luxury of glamping']
        ],
        'camping' => [
            'images' => [
                ['src' => asset('assets/images/carousel/BYOT-2.jpg'), 'title' => 'Camping Site Overview', 'desc' => 'Spacious camping area in nature'],
                ['src' => asset('assets/images/carousel/Car-camping3.jpg'), 'title' => 'Camping Facilities', 'desc' => 'Common areas and amenities for campers'],
                ['src' => asset('assets/images/carousel/games-grounds4.jpg'), 'title' => 'Camping Activities', 'desc' => 'Fun activities available at the campsite']
            ],
            'info' => ['title' => 'Camping VR Experience', 'desc' => 'Explore the natural camping environment']
        ]
    ];
    @endphp

    @php
    $galleryData = [
        'glamping-standard' => [
            'images' => [
                asset('assets/images/carousel/Glamping-1.jpg'),
                asset('assets/images/carousel/wheretostay.jpg'),
                asset('assets/images/carousel/Calaia-26.jpg'),
                asset('assets/images/carousel/our-farm.jpg'),
                asset('assets/images/carousel/our-farm2.jpg'),
                asset('assets/images/carousel/f&b.jpg'),
                asset('assets/images/carousel/menu1.jpg'),
                asset('assets/images/carousel/menu3.jpg'),
                asset('assets/images/carousel/organic.png'),
                asset('assets/images/carousel/organic1.jpg'),
                asset('assets/images/carousel/farm.jpg'),
                asset('assets/images/carousel/activities-1.jpg')
            ]
        ],
        'glamping-premium' => [
            'images' => [
                asset('assets/images/carousel/Glamping-1.jpg'),
                asset('assets/images/carousel/wheretostay.jpg'),
                asset('assets/images/carousel/Calaia-26.jpg'),
                asset('assets/images/carousel/our-farm.jpg'),
                asset('assets/images/carousel/our-farm2.jpg'),
                asset('assets/images/carousel/f&b.jpg'),
                asset('assets/images/carousel/menu1.jpg'),
                asset('assets/images/carousel/menu3.jpg'),
                asset('assets/images/carousel/organic.png'),
                asset('assets/images/carousel/organic1.jpg'),
                asset('assets/images/carousel/farm.jpg'),
                asset('assets/images/carousel/activities-1.jpg')
            ]
        ],
        'camping-dome' => [
            'images' => [
                asset('assets/images/carousel/BYOT-2.jpg'),
                asset('assets/images/carousel/Car-camping3.jpg'),
                asset('assets/images/carousel/bonfire-5.jpg'),
                asset('assets/images/carousel/biking-4.jpg'),
                asset('assets/images/carousel/games-grounds4.jpg'),
                asset('assets/images/carousel/go-cart2.jpg'),
                asset('assets/images/carousel/minikalesa.jpg'),
                asset('assets/images/carousel/animal and wellness.jpg'),
                asset('assets/images/carousel/groups.jpg'),
                asset('assets/images/carousel/whattodo.jpg'),
                asset('assets/images/carousel/SET MEALS2.jpg'),
                asset('assets/images/carousel/f&b.jpg')
            ]
        ],
        'camping-bell' => [
            'images' => [
                asset('assets/images/carousel/BYOT-2.jpg'),
                asset('assets/images/carousel/Car-camping3.jpg'),
                asset('assets/images/carousel/bonfire-5.jpg'),
                asset('assets/images/carousel/biking-4.jpg'),
                asset('assets/images/carousel/games-grounds4.jpg'),
                asset('assets/images/carousel/go-cart2.jpg'),
                asset('assets/images/carousel/minikalesa.jpg'),
                asset('assets/images/carousel/animal and wellness.jpg'),
                asset('assets/images/carousel/groups.jpg'),
                asset('assets/images/carousel/whattodo.jpg'),
                asset('assets/images/carousel/SET MEALS2.jpg'),
                asset('assets/images/carousel/f&b.jpg')
            ]
        ],
        'camping-byot' => [
            'images' => [
                asset('assets/images/carousel/BYOT-2.jpg'),
                asset('assets/images/carousel/Car-camping3.jpg'),
                asset('assets/images/carousel/bonfire-5.jpg'),
                asset('assets/images/carousel/biking-4.jpg'),
                asset('assets/images/carousel/games-grounds4.jpg'),
                asset('assets/images/carousel/go-cart2.jpg'),
                asset('assets/images/carousel/minikalesa.jpg'),
                asset('assets/images/carousel/animal and wellness.jpg'),
                asset('assets/images/carousel/groups.jpg'),
                asset('assets/images/carousel/whattodo.jpg'),
                asset('assets/images/carousel/SET MEALS2.jpg'),
                asset('assets/images/carousel/f&b.jpg')
            ]
        ],
        'camping-car' => [
            'images' => [
                asset('assets/images/carousel/BYOT-2.jpg'),
                asset('assets/images/carousel/Car-camping3.jpg'),
                asset('assets/images/carousel/bonfire-5.jpg'),
                asset('assets/images/carousel/biking-4.jpg'),
                asset('assets/images/carousel/games-grounds4.jpg'),
                asset('assets/images/carousel/go-cart2.jpg'),
                asset('assets/images/carousel/minikalesa.jpg'),
                asset('assets/images/carousel/animal and wellness.jpg'),
                asset('assets/images/carousel/groups.jpg'),
                asset('assets/images/carousel/whattodo.jpg'),
                asset('assets/images/carousel/SET MEALS2.jpg'),
                asset('assets/images/carousel/f&b.jpg')
            ]
        ]
    ];
    @endphp

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Pass PHP data to JavaScript --}}
    <script>
      window.vrData = <?php echo json_encode($vrData); ?>;
      window.galleryData = <?php echo json_encode($galleryData); ?>;
    </script>

    <script>
      // ---------- Alpine component ----------
      function staycationPage(){
        return {
          // modals 
          glampingOpen: false,
          campingOpen: false,
          vrOpen: false,
          vrType: '',
          vrCurrentIndex: 0,
          vrData: {},
          pannellumViewer: null,
          galleryOpen: false,
          galleryType: '',
          galleryOption: '',
          galleryImages: [],
          galleryCurrentIndex: 0,
          book(url){ window.location.href = url; },
          openModal(which){
            if(which==='glamping') this.glampingOpen = true; else this.campingOpen = true;
            document.body.style.overflow = 'hidden';
          },
          closeGlampingModal(){
            this.glampingOpen = false;
            document.body.style.overflow = '';
          },
          closeCampingModal(){
            this.campingOpen = false;
            document.body.style.overflow = '';
          },
          openVR(type){
            this.vrType = type;
            this.vrData = this.getVRData(type);
            this.vrCurrentIndex = 0;
            this.vrOpen = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => this.initVRViewer());
          },
          closeVR(){
            this.vrOpen = false;
            document.body.style.overflow = '';
            if(this.pannellumViewer) {
              this.pannellumViewer.destroy();
              this.pannellumViewer = null;
            }
          },
          vrPrev(){
            if(this.vrCurrentIndex > 0){
              this.vrCurrentIndex--;
              this.updateVRViewer();
            }
          },
          vrNext(){
            if(this.vrCurrentIndex < this.vrData.images.length - 1){
              this.vrCurrentIndex++;
              this.updateVRViewer();
            }
          },
          vrFullscreen(){
            if(this.pannellumViewer) this.pannellumViewer.toggleFullscreen();
          },
          toggleVRInfo(){
            const overlay = document.getElementById('vr-info-overlay');
            overlay.style.display = overlay.style.display === 'none' ? 'block' : 'none';
          },
          shareVR(){
            // Placeholder for share functionality
            alert('Share functionality not implemented yet.');
          },
          getVRData(type){
            return window.vrData[type];
          },
          initVRViewer(){
            if(typeof pannellum !== 'undefined'){
              this.pannellumViewer = pannellum.viewer('vr-media-container', {
                type: 'equirectangular',
                panorama: this.vrData.images[this.vrCurrentIndex].src,
                autoLoad: true,
                compass: false,
                hotSpots: []
              });
              this.updateVRCounter();
              this.updateVRInfo();
            }
          },
          updateVRViewer(){
            if(this.pannellumViewer){
              this.pannellumViewer.setPanorama(this.vrData.images[this.vrCurrentIndex].src);
              this.updateVRCounter();
              this.updateVRInfo();
            }
          },
          updateVRCounter(){
            document.getElementById('vr-counter').textContent = `${this.vrCurrentIndex + 1} / ${this.vrData.images.length}`;
          },
          updateVRInfo(){
            document.getElementById('vr-info-title').textContent = this.vrData.images[this.vrCurrentIndex].title;
            document.getElementById('vr-info-description').textContent = this.vrData.images[this.vrCurrentIndex].desc;
          },
          openGallery(type, option){
            this.galleryType = type;
            this.galleryOption = option;
            const data = this.getGalleryData(type + '-' + option);
            this.galleryImages = data.images || [];
            this.galleryCurrentIndex = 0;
            this.galleryOpen = true;
            document.body.style.overflow = 'hidden';
          },
          closeGallery(){
            this.galleryOpen = false;
            document.body.style.overflow = '';
          },
          galleryPrev(){
            if(this.galleryCurrentIndex > 0) this.galleryCurrentIndex--;
          },
          galleryNext(){
            if(this.galleryCurrentIndex < this.galleryImages.length - 1) this.galleryCurrentIndex++;
          },
          getGalleryData(key){
            return window.galleryData[key] || {};
          }
        }
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

