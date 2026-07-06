{{-- resources/views/components/hero-slider.blade.php --}}
@props([
    'images' => [],
    'texts' => []
])

{{-- Swiper CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<div class="swiper heroSwiper w-full h-64 sm:h-80 md:h-96 lg:h-125 xl:h-150" data-slides-count="{{ count($images) }}">
  <div class="swiper-wrapper">
    @if(count($images) > 0)
      @php $texts_array = array_values($texts); $labels_array = array_keys($texts); @endphp
      @foreach($images as $index => $image)
      @php
        $imagePath = $image->image_path ?? $image;
        $isPrimarySlide = $index === 0;
        $optimizedSrc = \App\Helpers\CloudinaryHelper::swiperSlide($imagePath) ?? \App\Helpers\ImageHelper::src($imagePath);
        $optimizedSrcset = \App\Helpers\CloudinaryHelper::srcset($imagePath);
      @endphp
      <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
        @if(str_contains($image->image_path ?? $image, '/video/'))
          <video
            src="{{ \App\Helpers\ImageHelper::src($imagePath) }}"
            class="w-full h-full object-cover object-center"
            autoplay
            muted
            loop
            playsinline
            preload="metadata"
          ></video>
        @else
          <img
            src="{{ $optimizedSrc }}"
            @if($optimizedSrcset)
              srcset="{{ $optimizedSrcset }}"
              sizes="100vw"
            @endif
            alt="{{ $image->title ?? 'Slide Image' }}"
            class="w-full h-full object-cover object-center"
            loading="{{ $isPrimarySlide ? 'eager' : 'lazy' }}"
            fetchpriority="{{ $isPrimarySlide ? 'high' : 'auto' }}"
            decoding="async"
            onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';"
          >
        @endif
        <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
        <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
          <div class="text-center px-4">
            @if(isset($texts_array[$index]))
              <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-6xl font-bold mb-4 uppercase"
                  style="">
                {{ ucfirst($labels_array[$index]) }}
              </h2>
              <p class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl uppercase"
                style=" font-style: italic;">
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
                style="">
              No Image Available
            </h2>
            <p class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl uppercase"
              style=" font-style: italic;">
              Images will be added soon
            </p>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>

{{-- Swiper JS --}}
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

@verbatim
<script>
document.addEventListener('DOMContentLoaded', function () {
  const heroSliders = document.querySelectorAll('.heroSwiper');
  heroSliders.forEach(function(slider) {
    const slidesCount = parseInt(slider.dataset.slidesCount);
    const swiper = new Swiper(slider, {
      autoplay: {
        delay: 10000,
        disableOnInteraction: false,
      },
      loop: slidesCount > 1,
      effect: 'fade',
      fadeEffect: {
        crossFade: true
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
      },
    });
  });
});
</script>
@endverbatim
