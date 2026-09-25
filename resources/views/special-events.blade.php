@php($contactDetails = \App\Models\InformationPage::contactDetails())

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
    <x-hero-slider :images="$swiperImages" :texts="$heroTexts ?? []" />

    {{-- ===== Events Section ===== --}}
    <main id="main">
      <section class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="max-w-7xl mx-auto">
          <x-section-header
            :title="$specialEventsPageTitle ?? 'Our Special Events'"
            :subtitle="$specialEventsPageIntro ?? 'From intimate weddings to grand corporate celebrations, our beautiful farm setting provides the perfect backdrop for your special occasion.'"
          />

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
            @php
              $specialEventsCtaPrimaryTarget = str_starts_with((string) ($specialEventsCtaPrimaryUrl ?? ''), 'http') ? '_blank' : '_self';
              $specialEventsCtaPrimaryRel = str_starts_with((string) ($specialEventsCtaPrimaryUrl ?? ''), 'http') ? 'noopener noreferrer' : '';
              $specialEventsCtaSecondaryTarget = str_starts_with((string) ($specialEventsCtaSecondaryUrl ?? ''), 'http') ? '_blank' : '_self';
              $specialEventsCtaSecondaryRel = str_starts_with((string) ($specialEventsCtaSecondaryUrl ?? ''), 'http') ? 'noopener noreferrer' : '';
            @endphp
            <h3 class="text-2xl md:text-3xl font-bold text-[#8f5d54] mb-4" style="">
              {{ $specialEventsCtaTitle ?? 'Have a Custom Event in Mind?' }}
            </h3>
            <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto" style="">
              {{ $specialEventsCtaIntro ?? 'We specialize in creating bespoke experiences tailored to your unique vision. From intimate gatherings to grand celebrations, let us bring your dream event to life.' }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
              <a
                href="{{ $specialEventsCtaPrimaryUrl ?? 'https://m.me/410774518796277' }}"
                target="{{ $specialEventsCtaPrimaryTarget }}"
                @if($specialEventsCtaPrimaryRel) rel="{{ $specialEventsCtaPrimaryRel }}" @endif
                class="inline-flex items-center justify-center gap-2 bg-brand text-white px-8 py-4 rounded-xl font-semibold hover:bg-brand transition-colors duration-300"
              >
                <i class="bi bi-messenger"></i>
                {{ $specialEventsCtaPrimaryLabel ?? 'Plan Custom Event' }}
              </a>
              <a
                href="{{ $specialEventsCtaSecondaryUrl ?? 'https://mail.google.com/mail/?view=cm&fs=1&to='.rawurlencode($contactDetails['email']) }}"
                target="{{ $specialEventsCtaSecondaryTarget }}"
                @if($specialEventsCtaSecondaryRel) rel="{{ $specialEventsCtaSecondaryRel }}" @endif
                class="inline-flex items-center justify-center gap-2 border-2 border-brand text-brand px-8 py-4 rounded-xl font-semibold hover:bg-brand hover:text-white transition-colors duration-300"
              >
                <i class="bi bi-envelope"></i>
                {{ $specialEventsCtaSecondaryLabel ?? 'Email Us Now' }}
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

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    @endpush
</x-page-layout>
