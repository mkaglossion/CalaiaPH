@php
  $sectionBackground = \App\Models\TestimonialSectionSetting::current();
  $bgImage = $sectionBackground?->background_image_path;
  $bgImageUrl = $bgImage
    ? \App\Helpers\CloudinaryHelper::swiperSlide($bgImage)
    : null;
@endphp

@if($testimonials->count() > 0)
<section class="relative py-12 min-h-125"
  style="
    @if($bgImageUrl)
      background: linear-gradient(rgba(143, 93, 84, 0.5), rgba(143, 93, 84, 0.5)), url('{{ e($bgImageUrl) }}');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
    @else
      background: linear-gradient(135deg, rgba(143, 93, 84, 0.88), rgba(106, 74, 47, 0.92));
    @endif
  ">

  <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-10">
      <h2 class="mt-8 font-bold tracking-tight text-white uppercase" style="font-size: 25px;">
        WHAT OUR GUESTS SAY ABOUT CALAIA
      </h2>
      <p class="mt-3 text-white" style="font-style: italic; font-size: 13px;">
        Hear from those who've experienced Calaia.
      </p>
      <div class="mt-6 mx-auto h-px w-80 bg-white"></div>
    </div>

  {{-- Swiper Slider --}}
  <div class="relative pb-1" data-testimonials-carousel>
    <div class="swiper testimonialsSwiper">
        <div class="swiper-wrapper">
          @foreach($testimonials as $idx => $testimonial)
            <div class="swiper-slide">
              <article
                class="w-70 sm:w-80 md:w-90 mx-auto
                       bg-white rounded-[28px]
                       px-7 py-8 sm:px-9 sm:py-10
                       text-center
                       shadow-[0_10px_30px_rgba(0,0,0,0.12)]
                       transition-transform duration-300 hover:scale-105"
              >
                {{-- avatar circle (like screenshot) --}}
                <div class="mx-auto mb-7 w-20 h-20 rounded-full overflow-hidden bg-[#7a7a7a] flex items-center justify-center">
                  @php
                    $testimonialImage = $testimonial->image_path ?: $testimonial->avatar;
                    $testimonialImageUrl = $testimonialImage
                      ? \App\Helpers\CloudinaryHelper::thumbnail($testimonialImage, 300)
                      : null;
                  @endphp
                  @if($testimonialImageUrl)
                    <img
                      src="{{ $testimonialImageUrl }}"
                      alt="{{ $testimonial->name }}"
                      class="w-full h-full object-cover"
                      loading="lazy"
                      decoding="async"
                    >
                  @else
                    <i data-lucide="user" class="w-6 h-6 text-white/70"></i>
                  @endif
                </div>

                {{-- quote (minimal + italic) --}}
                <p class="text-[#8f5d54] italic leading-[1.8]" style="font-size: 12px;">
                  "{{ $testimonial->content }}"
                </p>

                {{-- star rating --}}
                <div class="mt-4 flex justify-center">
                  @for($i = 1; $i <= 5; $i++)
                    <svg class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-[#8f5d54]' : 'text-gray-300' }}"
                         fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                  @endfor
                </div>

                {{-- optional name/role (keep subtle) --}}
                <div class="mt-4">
                  <p class="font-semibold text-[#8f5d54]" style="font-size: 12px;">
                    {{ $testimonial->name }}
                  </p>
                  <p class="text-[#8f5d54]/80" style="font-size: 11px;">
                    {{ $testimonial->position }}
                  </p>
                </div>
              </article>
            </div>
          @endforeach
        </div>

        {{-- Navigation buttons --}}
        <div class="swiper-button-next text-[#8f5d54]! w-12! h-12! right-4! md:right-8!"></div>
        <div class="swiper-button-prev text-[#8f5d54]! w-12! h-12! left-4! md:left-8!"></div>

    </div>

    {{-- Pagination sits outside the card/swiper so it never overlaps testimonial content. --}}
    <div class="testimonials-pagination" aria-label="Testimonial slides"></div>
    </div>

    {{-- Swiper CSS & JS --}}
    @once('swiper-css')
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @endonce
    @once('swiper-js')
      <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
    @endonce

    {{-- Custom Swiper Styles --}}
    <style>
        /* Pagination dots */
        [data-testimonials-carousel] .testimonials-pagination {
            position: static;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 0.75rem;
            margin-top: 1.25rem;
        }
        [data-testimonials-carousel] .testimonials-pagination .swiper-pagination-bullet {
            background-color: #8f5d54 !important;
            opacity: 0.3;
            margin: 0 !important;
        }
        [data-testimonials-carousel] .testimonials-pagination .swiper-pagination-bullet-active {
            background-color: #8f5d54 !important;
            opacity: 1 !important;
        }

        /* Navigation arrows */
        .testimonialsSwiper .swiper-button-next,
        .testimonialsSwiper .swiper-button-prev {
            color: #8f5d54 !important;
        }
        .testimonialsSwiper .swiper-button-next svg,
        .testimonialsSwiper .swiper-button-prev svg {
            fill: #8f5d54 !important;
            stroke: #8f5d54 !important;
        }
    </style>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-testimonials-carousel]').forEach(function (carousel) {
          const swiperElement = carousel.querySelector('.testimonialsSwiper');
          if (!swiperElement) return;

          new Swiper(swiperElement, {
            slidesPerView: 1,
            spaceBetween: 20,
            centeredSlides: false,
            loop: true,
            autoplay: {
              delay: 5000,
              disableOnInteraction: false,
            },
            pagination: {
              el: carousel.querySelector('.testimonials-pagination'),
              clickable: true,
            },
            navigation: {
              nextEl: carousel.querySelector('.swiper-button-next'),
              prevEl: carousel.querySelector('.swiper-button-prev'),
            },
            breakpoints: {
              640: {
                slidesPerView: 2,
                spaceBetween: 30,
              },
              1024: {
                slidesPerView: 3,
                spaceBetween: 40,
              },
            },
          });
        });
      });
    </script>

  </div>
</section>
@endif
