<x-page-layout 
    title="CalaiaPH - Special Events"
    description="Create unforgettable special events at CalaiaPH. From weddings to corporate retreats, our beautiful farm setting provides the perfect backdrop for your celebration."
    keywords="special events, weddings, corporate events, celebrations, CalaiaPH events, Philippines special occasions"
    activeHeader="special-events"
    bodyClass="bg-[#f5efe7]"
>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @endpush

     {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

    {{-- ===== Events Section ===== --}}
    <main id="main">
      <section class="container mx-auto px-4 py-12">
        <div class="max-w-7xl mx-auto">
          <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-brand mb-4">
              Our Special Events
            </h2>
            <p class="mt-6 text-lg text-brand max-w-3xl mx-auto">
              From intimate weddings to grand corporate celebrations, our beautiful farm setting provides the perfect backdrop for your special occasion.
            </p>
            <hr class="border-t-2 border-[#a97464] mt-6 mb-10 w-180 mx-auto rounded-full">
          </div>

          @if($events->count() > 0)
            {{-- Events Grid --}}
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
              @foreach($events as $event)
                <x-event-card :event="$event" />
              @endforeach
            </div>
          @else
            {{-- No Events Message --}}
            <div class="text-center py-16">
              <div class="max-w-md mx-auto">
                <i class="bi bi-calendar-x text-6xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-4" style="">
                  Events Coming Soon
                </h3>
                <p class="text-gray-600 mb-8" style="">
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
            <h3 class="text-2xl md:text-3xl font-bold text-[#8f5d54] mb-4" style="">
              Have a Custom Event in Mind?
            </h3>
            <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto" style="">
              We specialize in creating bespoke experiences tailored to your unique vision. From intimate gatherings to grand celebrations, let us bring your dream event to life.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
              <a
                href="mailto:calaia.ph@gmail.com?subject=Custom Event Planning Inquiry"
                class="inline-flex items-center justify-center gap-2 bg-brand text-white px-8 py-4 rounded-xl font-semibold hover:bg-brand transition-colors duration-300"
              >
                <i class="bi bi-messenger"></i>
                Plan Custom Event
              </a>
              <a
                href="tel:+639051294741"
                class="inline-flex items-center justify-center gap-2 border-2 border-brand text-brand px-8 py-4 rounded-xl font-semibold hover:bg-brand hover:text-white transition-colors duration-300"
              >
                <i class="bi bi-envelope"></i>
                Email Us Now
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

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    @endpush
</x-page-layout>