<x-page-layout 
    title="CalaiaPH - Activities"
    description="Discover thrilling outdoor activities at CalaiaPH. From biking trails to adventure sports, experience the best of nature-based recreation and team-building activities."
    keywords="outdoor activities, biking trails, adventure sports, team building, nature recreation, CalaiaPH, Philippines activities"
    activeHeader="activities"
    bodyClass="bg-[#f5efe7]"
>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @endpush

   {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$heroTexts" />

    {{-- ========= PACKAGES SECTION (MERGED FROM /packages) ========= --}}
    <main id="main">
      <section class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="max-w-7xl mx-auto">
          <x-section-header
            :title="$packagesSectionTitle ?? 'Day Packages'"
            :subtitle="$packagesSectionIntro ?? 'Start with our curated package options, then explore activities you can add to your stay.'"
            dividerClass="bg-brand"
          />

          @if($packages->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 auto-rows-max">
              @foreach($packages as $package)
                <div class="h-full">
                  <x-package-card :package="$package" variant="frontend" />
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-16">
              <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">No Packages Available</h3>
              <p class="text-gray-600">Please check back soon for new package offerings.</p>
            </div>
          @endif
        </div>
      </section>

    {{-- ========= ACTIVITIES SECTION ========= --}}
      <section class="container mx-auto px-4 py-12">
        <div class="max-w-300 mx-auto">

          <h2 class="text-3xl md:text-4xl font-bold text-brand mb-4 text-center">
            Enhance your stay with our fun-filled activities.
          </h2>
          <hr class="border-t-2 border-brand mt-6 mb-10 w-180 mx-auto rounded-full">

          <div class="space-y-12">

            {{-- ADVENTURE --}}
            <div id="adventure">
              <h3 class="font-bold uppercase tracking-wide mb-4 section-heading">
                Adventure
              </h3>

              <div id="adventure-carousel" class="relative" aria-roledescription="carousel">
                <div class="relative bg-[#f0e8df] rounded-2xl p-2 overflow-hidden">
                  <button type="button" aria-label="Previous slide"
                          data-role="prev"
                          class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-brand text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide"
                          data-role="next"
                          class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-brand text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>

                  <div class="min-h-80" id="adventure-stage"></div>
                </div>
                <div id="adventure-dots" class="flex gap-2 justify-center py-3" role="tablist" aria-label="Adventure slides"></div>
              </div>
            </div>

            {{-- SPORTS AND GAMES --}}
            <div id="sports-games">
              <h3 class="font-bold uppercase tracking-wide mb-4 section-heading">
                Sports &amp; Games
              </h3>

              <div id="sports-carousel" class="relative" aria-roledescription="carousel">
                <div class="relative bg-[#f0e8df] rounded-2xl p-2 overflow-hidden">
                  <button type="button" aria-label="Previous slide"
                          data-role="prev"
                          class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-brand text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide"
                          data-role="next"
                          class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-brand text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>

                  <div class="min-h-80" id="sports-stage"></div>
                </div>
                <div id="sports-dots" class="flex gap-2 justify-center py-3" role="tablist" aria-label="Sports &amp; games slides"></div>
              </div>
            </div>

            {{-- R & R + CAMP --}}
            <div id="rr-camp">
              <h3 class="font-bold uppercase tracking-wide mb-4 section-heading">
                R &amp; R &amp; Camp
              </h3>

              <div id="rrcamp-carousel" class="relative" aria-roledescription="carousel">
                <div class="relative bg-[#f0e8df] rounded-2xl p-2 overflow-hidden">
                  <button type="button" aria-label="Previous slide"
                          data-role="prev"
                          class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-brand text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide"
                          data-role="next"
                          class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-brand text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>

                  <div class="min-h-80" id="rrcamp-stage"></div>
                </div>
                <div id="rrcamp-dots" class="flex gap-2 justify-center py-3" role="tablist" aria-label="R &amp; R &amp; Camp slides"></div>
              </div>
            </div>

            {{-- FARM --}}
            <div id="farm">
              <h3 class="font-bold uppercase tracking-wide mb-4 section-heading">
                Farm
              </h3>

              <div id="farm-carousel" class="relative" aria-roledescription="carousel">
                <div class="relative bg-[#f0e8df] rounded-2xl p-2 overflow-hidden">
                  <button type="button" aria-label="Previous slide"
                          data-role="prev"
                          class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-brand text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide"
                          data-role="next"
                          class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-brand text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>

                  <div class="min-h-80" id="farm-stage"></div>
                </div>
                <div id="farm-dots" class="flex gap-2 justify-center py-3" role="tablist" aria-label="Farm slides"></div>
              </div>
            </div>

          </div>

          {{-- Shared slide template for all carousels --}}
          <template id="slide-template">
            <div class="slide grid grid-cols-1 gap-0 transition ease-in-out"
                 style="transition-duration: .28s">
              {{-- Left panel --}}
              <article class="panel relative overflow-hidden rounded-none shadow-none">
                <img alt=""
                     class="w-full block object-cover"
                     style="height: 520px; transform: scale(1); transition: transform .5s ease;">
                <div class="absolute inset-0 pointer-events-none"
                     style="background: linear-gradient(180deg, rgba(0,0,0,0) 50%, rgba(0,0,0,.35) 85%);"></div>
                <div class="absolute left-3 bottom-3 flex flex-col items-start gap-1">
                  <span class="bg-brand text-white font-extrabold text-[13px] tracking-[.08em] uppercase rounded-[10px] py-2.25 px-3 leading-none cursor-pointer"></span>
                  <h4 class="text-white text-lg sm:text-xl font-bold leading-tight drop-shadow-md"></h4>
                  <p class="max-w-104 text-sm sm:text-base text-white/90 leading-relaxed drop-shadow-md"></p>
                  <a class="text-white text-[14px] tracking-[.08em] uppercase py-2.25 px-3 no-underline hover:underline cursor-pointer">Learn more >></a>
                  <form action="{{ route('cart.add') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="title" value="">
                    <input type="hidden" name="price" value="">
                    <input type="hidden" name="image" value="">
                    <input type="hidden" name="quantity" value="1">
                  </form>
                </div>
              </article>

              {{-- Right panel --}}
              <article class="panel right-panel relative overflow-hidden rounded-none shadow-none">
                <img alt=""
                     class="w-full block object-cover"
                     style="height: 520px; transform: scale(1); transition: transform .5s ease;">
                <div class="absolute inset-0 pointer-events-none"
                     style="background: linear-gradient(180deg, rgba(0,0,0,0) 50%, rgba(0,0,0,.35) 85%);"></div>
                <div class="absolute left-3 bottom-3 flex flex-col items-start gap-1">
                  <span class="bg-brand text-white font-extrabold text-[13px] tracking-[.08em] uppercase rounded-[10px] py-2.25 px-3 leading-none cursor-pointer"></span>
                  <h4 class="text-white text-lg sm:text-xl font-bold leading-tight drop-shadow-md"></h4>
                  <p class="max-w-104 text-sm sm:text-base text-white/90 leading-relaxed drop-shadow-md"></p>
                  <a class="text-white text-[14px] tracking-[.08em] uppercase py-2.25 px-3 no-underline hover:underline cursor-pointer">Learn more >></a>
                  <form action="{{ route('cart.add') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="title" value="">
                    <input type="hidden" name="price" value="">
                    <input type="hidden" name="image" value="">
                    <input type="hidden" name="quantity" value="1">
                  </form>
                </div>
              </article>
            </div>
          </template>
        </div>
      </section>
    </main>
    {{-- ========= END ACTIVITIES SECTION ========= --}}



    {{-- JSON for each category --}}
    <script type="application/json" id="slides-data-adventure">
      {!! json_encode($slidesByCategory['adventure'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/json" id="slides-data-sports">
      {!! json_encode($slidesByCategory['sports'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/json" id="slides-data-rrcamp">
      {!! json_encode($slidesByCategory['rrcamp'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/json" id="slides-data-farm">
      {!! json_encode($slidesByCategory['farm'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    {{-- Alpine-powered Details Modal --}}
    <div x-data="{ open:false, modal:{ title:'', details:[], images:[], price:0, id:'' } }"
         x-on:open-activity.window="modal=$event.detail; open=true; $nextTick(() => { initModalSwiper(); })"
         class="relative z-50">
      <div x-show="open" class="fixed inset-0 bg-black/50" x-transition.opacity></div>
      <div x-show="open"
           x-transition
           class="fixed inset-0 flex items-center justify-center p-4"
           style="display: none;">
        <div class="bg-white rounded-xl w-full max-w-3xl shadow-xl">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold" x-text="modal.title + ' Details'"></h3>
            <button class="p-2 hover:bg-gray-100 rounded-md" @click="open=false" aria-label="Close">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto">
            <div x-data="{i:0}" class="relative">
              <template x-if="modal.images && modal.images.length">
                <div>
                  <img :src="modal.images[i]" :alt="modal.title + ' image ' + (i+1)"
                       class="w-full h-75 object-cover rounded-lg">
                  <button class="absolute top-1/2 -translate-y-1/2 left-2 grid place-items-center w-9 h-9 rounded-full bg-black/60 text-white"
                          @click="i = (i-1+modal.images.length)%modal.images.length">
                    <i class="fa-solid fa-chevron-left text-sm"></i>
                  </button>
                  <button class="absolute top-1/2 -translate-y-1/2 right-2 grid place-items-center w-9 h-9 rounded-full bg-black/60 text-white"
                          @click="i = (i+1)%modal.images.length">
                    <i class="fa-solid fa-chevron-right text-sm"></i>
                  </button>
                  <div class="flex gap-2 justify-center mt-2">
                    <template x-for="(img, idx) in modal.images" :key="idx">
                      <button class="w-2.5 h-2.5 rounded-full"
                              :class="idx===i ? 'bg-brand w-4' : 'bg-[#c8a89f]'"
                              @click="i=idx"></button>
                    </template>
                  </div>
                </div>
              </template>
            </div>

            <template x-if="modal.price > 0">
              <div class="text-lg font-bold text-brand">
                Price: ₱<span x-text="modal.price"></span>
              </div>
            </template>

            <div class="space-y-2">
              <template x-for="(p, idx) in modal.details" :key="idx">
                <p class="modal-detail">
                  <i class="fa-solid fa-circle-check text-brand mr-2"></i>
                  <span x-text="p"></span>
                </p>
              </template>
            </div>
          </div>

          <div class="px-5 py-4 border-t flex flex-col md:flex-row items-center justify-end gap-3">
            <template x-if="modal.price > 0">
              <a href="{{ route('booking.calendar') }}?type=daytour" class="bg-brand text-white px-6 py-3 rounded-2xl hover:bg-[#a0675a] transition-colors duration-300 w-full md:w-auto font-semibold text-center inline-flex items-center justify-center">
                <i class="bi bi-calendar-event mr-2"></i>
                Book Now
              </a>
            </template>
            <button type="button" @click="open=false" class="bg-gray-500 text-white px-6 py-3 rounded-2xl hover:bg-gray-600 transition-colors duration-300 w-full md:w-auto font-semibold">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
    {{-- ===== End Modal ===== --}}

    {{-- Floating Buttons --}}
    <x-floating-buttons />

    {{-- Swiper JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    {{-- Activities + Hero JS --}}
    <script>
      // Global helper for opening modal
      function viewDetails(title, details = [], images = [], price = 0, id = '') {
        window.dispatchEvent(new CustomEvent('open-activity', {
          detail: { title, details, images, price, id }
        }));
      }

      // Initialize modal swiper
      function initModalSwiper() {
        const modalSwiperEl = document.querySelector('.modal-swiper');
        if (!modalSwiperEl) return;

        // Destroy existing swiper if it exists
        if (window.modalSwiperInstance) {
          window.modalSwiperInstance.destroy();
        }

        const images = modalSwiperEl.querySelectorAll('.swiper-slide');
        if (images.length > 0) {
          window.modalSwiperInstance = new Swiper('.modal-swiper', {
            loop: images.length > 1,
            autoplay: {
              delay: 3000,
              disableOnInteraction: false,
            },
            pagination: {
              el: '.modal-pagination',
              clickable: true,
            },
            navigation: {
              nextEl: '.modal-next',
              prevEl: '.modal-prev',
            },
            a11y: {
              prevSlideMessage: 'Previous slide',
              nextSlideMessage: 'Next slide',
            },
          });
        }
      }

      function initActivityCarousel(config) {
        const dataEl = document.getElementById(config.dataId);
        if (!dataEl) return;

        let slides;
        try {
          slides = JSON.parse(dataEl.textContent || '[]');
        } catch (e) {
          slides = [];
        }
        if (!slides.length) return;

        const root   = document.getElementById(config.rootId);
        const stage  = document.getElementById(config.stageId);
        const dotsEl = document.getElementById(config.dotsId);
        const tpl    = document.getElementById('slide-template');

        if (!root || !stage || !dotsEl || !tpl) return;

        let index = 0;
        const count = slides.length;

        function open(item) {
          viewDetails(item.title, item.details || [], item.images || [], item.price || 0, item.id || item.title);
        }

        function wire(panelEl, item) {
           const img  = panelEl.querySelector('img');
           const chip = panelEl.querySelector('span');
           const title = panelEl.querySelector('h4');
           const summary = panelEl.querySelector('p');
           const link = panelEl.querySelector('a');
           const form = panelEl.querySelector('form');

           img.src = item.img;
           img.alt = item.alt || item.title;
           img.style.cursor = 'pointer';
           chip.textContent = item.title;
           if (title) title.textContent = item.title || '';
           if (summary) summary.textContent = item.summary || item.details?.[0] || '';

           // Set form inputs
           if (form) {
             const idInput = form.querySelector('input[name="id"]');
             const titleInput = form.querySelector('input[name="title"]');
             const priceInput = form.querySelector('input[name="price"]');
             const imageInput = form.querySelector('input[name="image"]');
             if (idInput) idInput.value = item.title;
             if (titleInput) titleInput.value = item.title;
             if (priceInput) priceInput.value = item.price || 0;
             if (imageInput) imageInput.value = item.img || '';
           }

           [img, chip, link].forEach(el => {
             if (!el) return;
             el.addEventListener('click', (e) => {
               e.preventDefault();
               open(item);
             });
           });
         }

        function updateDots(active) {
          dotsEl.innerHTML = '';
          for (let i = 0; i < count; i++) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'h-2.5 rounded-full ' + (i === active ? 'bg-[#a75e3e] w-4' : 'bg-[#c8a89f] w-2.5');
            b.setAttribute('role', 'tab');
            b.setAttribute('aria-label', `Go to slide ${i + 1}`);
            b.addEventListener('click', () => goTo(i));
            dotsEl.appendChild(b);
          }
        }

        function render(i) {
          stage.querySelectorAll('.slide').forEach(n => n.remove());

          const frag   = tpl.content.cloneNode(true);
          const panels = frag.querySelectorAll('.panel');

          const L = slides[i].left;
          if (L) wire(panels[0], L);

          const R = slides[i].right;
          if (R) wire(panels[1], R);
          else panels[1].remove();

          const slideEl = frag.querySelector('.slide');
          slideEl.classList.add('sm:grid-cols-2');

          stage.appendChild(frag);
          updateDots(i);
        }

        function animate(delta) {
          const old = stage.querySelector('.slide');
          if (!old) {
            render(index);
            return;
          }
          old.style.opacity = '0';
          old.style.transform = `translateX(${delta > 0 ? -24 : 24}px)`;
          setTimeout(() => render(index), 180);
        }

        function go(delta) {
          index = (index + delta + count) % count;
          animate(delta);
        }

        function goTo(i) {
          const d = i > index ? 1 : -1;
          index = i;
          animate(d);
        }

        // Arrows
        const prevBtn = root.querySelector('[data-role="prev"]');
        const nextBtn = root.querySelector('[data-role="next"]');
        prevBtn && prevBtn.addEventListener('click', () => go(-1));
        nextBtn && nextBtn.addEventListener('click', () => go(1));

        // Keyboard
        root.setAttribute('tabindex', '0');
        root.addEventListener('keydown', (e) => {
          if (e.key === 'ArrowLeft') go(-1);
          if (e.key === 'ArrowRight') go(1);
        });

        // Touch
        let startX = null;
        stage.addEventListener('touchstart', e => {
          startX = e.touches[0].clientX;
        }, { passive: true });

        stage.addEventListener('touchend', e => {
          if (startX == null) return;
          const dx = e.changedTouches[0].clientX - startX;
          if (dx > 40) go(-1);
          if (dx < -40) go(1);
          startX = null;
        }, { passive: true });

        // Initial render
        render(index);
      }

        // Fix for flushLogs fetch error - aggressive override
        (function() {
          // Override flushLogs before any other scripts can define it
          Object.defineProperty(window, 'flushLogs', {
            value: function() {
              console.log('flushLogs called - disabled to prevent fetch errors');
              return Promise.resolve();
            },
            writable: false,
            enumerable: true,
            configurable: true
          });

          // Also override any scheduleFlush function that might be calling flushLogs
          Object.defineProperty(window, 'scheduleFlush', {
            value: function() {
              console.log('scheduleFlush called - disabled');
            },
            writable: false,
            enumerable: true,
            configurable: true
          });
        })();

       document.addEventListener('DOMContentLoaded', function () {
         // Hero Swiper
         const heroEl = document.querySelector('.heroSwiper');
         if (heroEl) {
           const slidesCount = parseInt(heroEl.dataset.slidesCount || '0', 10);
           new Swiper('.heroSwiper', {
             autoplay: {
               delay: 3000,
               disableOnInteraction: false,
             },
             loop: slidesCount > 1,
             effect: 'fade',
             fadeEffect: {
               crossFade: true
             },
             keyboard: {
               enabled: true,
             },
             a11y: {
               prevSlideMessage: 'Previous slide',
               nextSlideMessage: 'Next slide',
             },
           });
         }

         // Activity carousels by category
         const carousels = [
           { rootId: 'adventure-carousel', stageId: 'adventure-stage', dotsId: 'adventure-dots', dataId: 'slides-data-adventure' },
           { rootId: 'sports-carousel',    stageId: 'sports-stage',    dotsId: 'sports-dots',    dataId: 'slides-data-sports' },
           { rootId: 'rrcamp-carousel',    stageId: 'rrcamp-stage',    dotsId: 'rrcamp-dots',    dataId: 'slides-data-rrcamp' },
           { rootId: 'farm-carousel',      stageId: 'farm-stage',      dotsId: 'farm-dots',      dataId: 'slides-data-farm' },
         ];

         carousels.forEach(initActivityCarousel);
       });

      // Override handleBookNow for activities page to redirect to calendar
      window.handleBookNow = function() {
        const title = document.getElementById('detailsModalLabel').textContent.replace(' Details', '');
        const price = document.getElementById('detailsModalPriceDisplay')?.textContent || 'PHP 0';

        // Redirect to booking calendar with daytour type (activities are part of day tours)
        const url = '{{ route("booking.calendar") }}?type=daytour&package=' + encodeURIComponent(title) + '&price=' + encodeURIComponent(price);
        window.location.href = url;
      };
    </script>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    @endpush
</x-page-layout>
