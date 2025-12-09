{{-- Swiper CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<div class="swiper mySwiper w-full h-64 sm:h-80 md:h-96 lg:h-[500px] xl:h-[600px]">
  <div class="swiper-wrapper">
    @forelse($images ?? [] as $image)
      <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
        <img src="{{ $image->image_path }}" alt="{{ $image->title ?? 'Slider Image' }}" class="w-full h-full object-cover object-center" loading="lazy">
        <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
        <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
          <div class="text-center px-4">
            <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4">{{ $image->title }}</h2>
            <p class="text-base sm:text-lg md:text-xl lg:text-2xl uppercase italic font-vila-zilka">{{ $image->description ?? 'Experience the beauty of nature' }}</p>
          </div>
        </div>
      </div>
    @empty
      <div class="swiper-slide bg-gray-800 text-white flex items-center justify-center min-h-full">
        <div class="text-center px-4">
          <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4">Welcome to CalaiaPH</h2>
          <p class="text-base sm:text-lg md:text-xl lg:text-2xl uppercase italic font-vila-zilka">Experience the beauty of nature</p>
        </div>
      </div>
      <div class="swiper-slide bg-gray-700 text-white flex items-center justify-center min-h-full">
        <div class="text-center px-4">
          <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4">Adventure Awaits</h2>
          <p class="text-base sm:text-lg md:text-xl lg:text-2xl uppercase italic font-vila-zilka">Discover exciting activities</p>
        </div>
      </div>
      <div class="swiper-slide bg-gray-600 text-white flex items-center justify-center min-h-full">
        <div class="text-center px-4">
          <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4">Relax and Unwind</h2>
          <p class="text-base sm:text-lg md:text-xl lg:text-2xl uppercase italic font-vila-zilka">Perfect staycation destination</p>
        </div>
      </div>
    @endforelse
  </div>
  <div class="swiper-pagination hidden sm:block" aria-label="Slider pagination"></div>
</div>

{{-- Swiper JS --}}
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

@verbatim
<script>
document.addEventListener('DOMContentLoaded', function () {
  const swiper = new Swiper('.mySwiper', {
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    loop: true,
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
      paginationBulletMessage: 'Go to slide {{index}}',
    },
  });
});
</script>
@endverbatim