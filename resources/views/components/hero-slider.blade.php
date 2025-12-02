{{-- resources/views/components/hero-slider.blade.php --}}
@props([
    'images' => [],
    'texts' => []
])

{{-- Swiper CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<div class="swiper heroSwiper w-full h-64 sm:h-80 md:h-96 lg:h-[500px] xl:h-[600px]" data-slides-count="{{ count($images) }}">
  <div class="swiper-wrapper">
    @if(count($images) > 0)
      @php $texts_array = array_values($texts); $labels_array = array_keys($texts); @endphp
      @foreach($images as $index => $image)
      <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
        <img
          src="{{ $image->image_path ?? $image }}"
          alt="{{ $image->title ?? 'Slide Image' }}"
          class="w-full h-full object-cover object-center"
          loading="lazy"
        >
        <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
        <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
          <div class="text-center px-4">
            @if(isset($texts_array[$index]))
              <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-6xl font-bold mb-4 uppercase"
                  style="font-family: gotham;">
                {{ ucfirst($labels_array[$index]) }}
              </h2>
              <p class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl uppercase"
                style="font-family: 'Vilaka Modern Serif', serif; font-style: italic;">
                {{ $texts_array[$index] }}
              </p>
            @endif
          </div>
        </div>
      </div>
      @endforeach
    @else
      <div class="swiper-slide relative flex items-center justify-center bg-gray-200 overflow-hidden">
        <div class="absolute inset-0 flex items-center justify-center z-20 text-gray-600">
          <div class="text-center px-4">
            <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-6xl font-bold mb-4 uppercase"
                style="font-family: gotham;">
              No Image Available
            </h2>
            <p class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl uppercase"
              style="font-family: 'Vilaka Modern Serif', serif; font-style: italic;">
              Images will be added soon
            </p>
          </div>
        </div>
      </div>
    @endif
  </div>
  <div class="swiper-pagination hidden sm:block" aria-label="Slider pagination"></div>
</div>

{{-- Swiper JS --}}
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

@php $paginationMessage = 'Go to slide {{index}}'; @endphp

@verbatim
<script>
document.addEventListener('DOMContentLoaded', function () {
  const heroSliders = document.querySelectorAll('.heroSwiper');
  heroSliders.forEach(function(slider) {
    const slidesCount = parseInt(slider.dataset.slidesCount);
    const swiper = new Swiper(slider, {
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      loop: slidesCount > 1,
      effect: 'fade',
      fadeEffect: {
        crossFade: true
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      keyboard: {
        enabled: true,
        onlyInViewport: false,
      },
      a11y: {
        prevSlideMessage: 'Previous slide',
        nextSlideMessage: 'Next slide',
        firstSlideMessage: 'This is the first slide',
        lastSlideMessage: 'This is the last slide',
        paginationBulletMessage: '{{ $paginationMessage }}',
      },
    });
  });
});
</script>
@endverbatim