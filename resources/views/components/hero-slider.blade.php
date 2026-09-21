{{-- resources/views/components/hero-slider.blade.php --}}
@props([
    'images' => [],
    'texts' => [],
    'usePlaceholderOnError' => true,
])

{{-- Swiper CSS --}}
@once('swiper-css')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
@endonce

<div class="swiper heroSwiper w-full h-64 sm:h-80 md:h-96 lg:h-125 xl:h-150" data-slides-count="{{ count($images) }}">
  <div class="swiper-wrapper">
    @if(count($images) > 0)
      @php $texts_array = array_values($texts); $labels_array = array_keys($texts); @endphp
      @foreach($images as $index => $image)
      @php
        $imagePath = $image->image_path ?? $image;
        $isPrimarySlide = $index === 0;
        $isVideo = \App\Helpers\ImageHelper::isVideo($imagePath);
        $optimizedSrc = $isVideo ? null : (\App\Helpers\CloudinaryHelper::swiperSlide($imagePath) ?? \App\Helpers\ImageHelper::src($imagePath));
        $optimizedSrcset = $isVideo ? null : \App\Helpers\CloudinaryHelper::srcset($imagePath);
      @endphp
      <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
        @if($isVideo)
          @php
            $videoSrc = \App\Helpers\ImageHelper::videoMp4($imagePath) ?? \App\Helpers\ImageHelper::src($imagePath);
            $videoPoster = \App\Helpers\ImageHelper::videoThumbnail($imagePath) ?? \App\Helpers\ImageHelper::placeholder();
          @endphp
          <img
            src="{{ $videoPoster }}"
            alt=""
            aria-hidden="true"
            class="absolute inset-0 z-0 w-full h-full object-cover object-center"
            loading="{{ $isPrimarySlide ? 'eager' : 'lazy' }}"
            fetchpriority="{{ $isPrimarySlide ? 'high' : 'auto' }}"
            decoding="async"
            onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';"
          >
          <video
            class="absolute inset-0 z-0 w-full h-full object-cover object-center opacity-0 transition-opacity duration-300"
            src="{{ $videoSrc }}"
            autoplay
            muted
            loop
            playsinline
            webkit-playsinline
            preload="{{ $isPrimarySlide ? 'metadata' : 'none' }}"
            poster="{{ $videoPoster }}"
            data-hero-video
          >
            <source src="{{ $videoSrc }}" type="video/mp4">
          </video>
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
            @if($usePlaceholderOnError)
              onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';"
            @else
              onerror="this.remove();"
            @endif
          >
        @endif
        <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
        <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
          <div class="text-center px-4">
            @php
              $slideTitle = $image->title ?? ($labels_array[$index] ?? '');
              $slideDesc = $texts_array[$index] ?? '';
            @endphp
            @if($slideTitle || $slideDesc)
              <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-6xl font-bold mb-4 uppercase" style="">
                {{ $slideTitle ? ucfirst($slideTitle) : '' }}
              </h2>
              @if($slideDesc)
                <p class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl uppercase" style=" font-style: italic;">
                  {{ $slideDesc }}
                </p>
              @endif
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
@once('swiper-js')
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
@endonce

@verbatim
<script>
document.addEventListener('DOMContentLoaded', function () {
  function prepareHeroVideo(video) {
    video.muted = true;
    video.defaultMuted = true;
    video.playsInline = true;
    video.setAttribute('muted', '');
    video.setAttribute('playsinline', '');
    video.setAttribute('webkit-playsinline', '');
  }

  function showVideo(video) {
    video.classList.remove('opacity-0');
  }

  function showPoster(video) {
    video.classList.add('opacity-0');
  }

  function pauseHeroVideo(video) {
    video.pause();
    showPoster(video);
  }

  function playHeroVideo(video) {
    prepareHeroVideo(video);

    const requestPlay = function() {
      const playPromise = video.play();
      if (playPromise !== undefined) {
        playPromise.catch(function() {
          showPoster(video);
        });
      }
    };

    if (video.readyState >= 2) {
      requestPlay();
      return;
    }

    video.load();
    video.addEventListener('canplay', requestPlay, { once: true });
    video.addEventListener('loadeddata', requestPlay, { once: true });
  }

  function syncHeroVideos(slider) {
    slider.querySelectorAll('[data-hero-video]').forEach(function(video) {
      const activeSlide = video.closest('.swiper-slide-active');

      if (activeSlide) {
        playHeroVideo(video);
      } else {
        pauseHeroVideo(video);
      }
    });
  }

  document.querySelectorAll('[data-hero-video]').forEach(function(video) {
    const showVideo = function() {
      video.classList.remove('opacity-0');
    };
    const showPoster = function() {
      video.classList.add('opacity-0');
    };

    prepareHeroVideo(video);
    video.addEventListener('playing', showVideo);
    video.addEventListener('error', showPoster);
  });

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
      on: {
        init: function() {
          syncHeroVideos(slider);
        },
        slideChangeTransitionStart: function() {
          slider.querySelectorAll('[data-hero-video]').forEach(pauseHeroVideo);
        },
        slideChangeTransitionEnd: function() {
          syncHeroVideos(slider);
        },
      },
    });

    document.addEventListener('visibilitychange', function() {
      if (!document.hidden) {
        syncHeroVideos(slider);
      }
    });

    window.addEventListener('touchstart', function() {
      syncHeroVideos(slider);
    }, { once: true, passive: true });
  });
});
</script>
@endverbatim
