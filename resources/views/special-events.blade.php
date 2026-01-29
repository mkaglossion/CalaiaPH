<!doctype html>
<html lang="en">
  <head>
    @include('layouts.partials.favicon')
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Create unforgettable special events at CalaiaPH. From weddings to corporate retreats, our beautiful farm setting provides the perfect backdrop for your celebration.">
    <meta name="keywords" content="special events, weddings, corporate events, celebrations, CalaiaPH events, Philippines special occasions">
    <meta name="author" content="CalaiaPH">
    @vite(['resources/css/app.css'])
    <title>CalaiaPH - Special Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  </head>

  <body class="bg-[#f5efe7]">
      <div class="flex flex-col items-center justify-center px-4">
        @if (session('success'))
            <x-flash-message type="success" :message="session('success')" />
        @endif
      </div>
    <x-header active="special-events" />

     {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

    {{-- ===== Events Section ===== --}}
    <main id="main">
      <section class="container mx-auto px-4 py-12">
        <div class="max-w-7xl mx-auto">
          <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-brand mb-4" style="font-family: 'Gotham Book', sans-serif;">
              Our Special Events
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto" style="font-family: 'Gotham Book', sans-serif;">
              From intimate weddings to grand corporate celebrations, our beautiful farm setting provides the perfect backdrop for your special occasion.
            </p>
          </div>

          @if($events->count() > 0)
            {{-- Events Grid --}}
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
              @foreach($events as $event)
              <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                {{-- Event Image --}}
                <div class="relative h-64 overflow-hidden">
                  @if($event->swiperImages->count() > 0)
                    <img
                      src="{{ $event->swiperImages->first()->image_path }}"
                      alt="{{ $event->swiperImages->first()->title ?? $event->name }}"
                      class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                    >
                  @else
                    <div class="w-full h-full bg-linear-to-br from-[#8f5d54] to-[#a97464] flex items-center justify-center">
                      <i class="bi bi-calendar-event text-white text-4xl"></i>
                    </div>
                  @endif
                  <div class="absolute inset-0 bg-black bg-opacity-20"></div>
                  <div class="absolute top-4 left-4">
                    <span class="bg-brand text-white px-3 py-1 rounded-full text-sm font-semibold">
                      Special Event
                    </span>
                  </div>
                </div>

                {{-- Event Content --}}
                <div class="p-6">
                  <h3 class="text-xl font-bold text-gray-900 mb-3" style="font-family: 'Gotham Book', sans-serif;">
                    {{ $event->name }}
                  </h3>

                  @if($event->texts->count() > 0)
                    @foreach($event->texts as $text)
                      @if($text->section === 'description' || $text->title === 'Description')
                        <p class="text-gray-600 mb-4 leading-relaxed" style="font-family: 'Gotham Book', sans-serif;">
                          {{ Str::limit($text->content, 120) }}
                        </p>
                      @endif
                    @endforeach
                  @endif

                  {{-- Event Features --}}
                  <div class="mb-6">
                    <h4 class="font-semibold text-gray-900 mb-2" style="font-family: 'Gotham Book', sans-serif;">What's Included:</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                      @if($event->texts->count() > 0)
                        @foreach($event->texts as $text)
                          @if($text->section === 'features' || str_contains(strtolower($text->title), 'feature'))
                            @php $features = explode("\n", $text->content); @endphp
                            @foreach(array_slice($features, 0, 3) as $feature)
                              @if(trim($feature))
                                <li class="flex items-center">
                                  <i class="bi bi-check-circle-fill text-brand mr-2 text-xs"></i>
                                  {{ trim($feature) }}
                                </li>
                              @endif
                            @endforeach
                          @endif
                        @endforeach
                      @else
                        <li class="flex items-center">
                          <i class="bi bi-check-circle-fill text-brand mr-2 text-xs"></i>
                          Custom event planning
                        </li>
                        <li class="flex items-center">
                          <i class="bi bi-check-circle-fill text-brand mr-2 text-xs"></i>
                          Beautiful farm setting
                        </li>
                        <li class="flex items-center">
                          <i class="bi bi-check-circle-fill text-brand mr-2 text-xs"></i>
                          Professional coordination
                        </li>
                      @endif
                    </ul>
                  </div>

                  {{-- Contact Button --}}
                  <a
                    href="mailto:calaia.ph@gmail.com?subject=Inquiry about {{ $event->name }}"
                    class="w-full inline-flex items-center justify-center gap-2 bg-brand text-white px-6 py-3 rounded-xl font-semibold hover:bg-brand transition-colors duration-300"
                  >
                    <i class="bi bi-envelope"></i>
                    Inquire About This Event
                  </a>
                </div>
              </div>
              @endforeach
            </div>
          @else
            {{-- No Events Message --}}
            <div class="text-center py-16">
              <div class="max-w-md mx-auto">
                <i class="bi bi-calendar-x text-6xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-4" style="font-family: 'Gotham Book', sans-serif;">
                  Events Coming Soon
                </h3>
                <p class="text-gray-600 mb-8" style="font-family: 'Gotham Book', sans-serif;">
                  We're currently preparing some amazing special events for you. Check back soon or contact us for custom event planning.
                </p>
                <a
                  href="{{ route('home') }}#contact"
                  class="inline-flex items-center gap-2 bg-brand text-white px-6 py-3 rounded-xl font-semibold hover:bg-brand transition-colors duration-300"
                >
                  <i class="bi bi-envelope"></i>
                  Contact Us
                </a>
              </div>
            </div>
          @endif

          {{-- Call to Action Section --}}
          <div class="mt-16 bg-white rounded-2xl shadow-lg p-8 md:p-12 text-center">
            <h3 class="text-2xl md:text-3xl font-bold text-[#8f5d54] mb-4" style="font-family: 'Gotham Book', sans-serif;">
              Have a Custom Event in Mind?
            </h3>
            <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto" style="font-family: 'Gotham Book', sans-serif;">
              We specialize in creating bespoke experiences tailored to your unique vision. From intimate gatherings to grand celebrations, let us bring your dream event to life.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
              <a
                href="mailto:calaia.ph@gmail.com?subject=Custom Event Planning Inquiry"
                class="inline-flex items-center justify-center gap-2 bg-brand text-white px-8 py-4 rounded-xl font-semibold hover:bg-brand transition-colors duration-300"
              >
                <i class="bi bi-envelope"></i>
                Plan Custom Event
              </a>
              <a
                href="tel:+639051294741"
                class="inline-flex items-center justify-center gap-2 border-2 border-brand text-brand px-8 py-4 rounded-xl font-semibold hover:bg-brand hover:text-white transition-colors duration-300"
              >
                <i class="bi bi-telephone"></i>
                Call Us Now
              </a>
            </div>
          </div>
        </div>
      </section>

      {{-- SECTION: Visit or Contact Us --}}
      <x-contact-section />
    </main>

    {{-- Floating Buttons --}}
    <x-floating-buttons />

    {{-- Footer --}}
    <x-footer />

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>