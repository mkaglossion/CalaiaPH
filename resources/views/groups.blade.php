<!doctype html>
<html lang="en">
  <head>
    @include('layouts.partials.favicon')
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Plan unforgettable group experiences at CalaiaPH. Perfect for corporate retreats, family reunions, and team-building activities in a beautiful farm setting.">
    <meta name="keywords" content="group activities, corporate retreats, team building, family reunions, CalaiaPH groups, Philippines group events">
    <meta name="author" content="CalaiaPH">
    @vite(['resources/css/app.css'])
    <title>CalaiaPH - Groups</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  </head>
  <body class="bg-[#f5efe7]">
    {{-- flash message --}}
      <div class="flex flex-col items-center justify-center px-4">
        @if (session('success'))
            <x-flash-message type="success" :message="session('success')" />
        @endif
      </div>
    <x-header active="groups" />
    {{-- Hero Slider --}}
   <div class="swiper heroSwiper w-full h-64 sm:h-80 md:h-96 lg:h-125 xl:h-150" data-slides-count="{{ count($swiperImages) }}">
      <div class="swiper-wrapper">
        @if(count($swiperImages) > 0)
          @php $texts_array = array_values($texts); $labels_array = array_keys($texts); @endphp
          @foreach($swiperImages as $index => $image)
          <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
            <img
              src="{{ $image->image_path }}"
              alt="{{ $image->title ?? 'Slide Image' }}"
              class="w-full h-full object-cover object-center"
              loading="lazy"
            >
            <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
            <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
              <div class="text-center px-4">
                @if(isset($texts_array[$index]))
                  <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-6xl font-bold mb-4 uppercase">
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
                <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-6xl font-bold mb-4 uppercase">
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

    <script>
    document.addEventListener('DOMContentLoaded', function () {
      const slidesCount = parseInt(document.querySelector('.heroSwiper').dataset.slidesCount);
      // Initialize hero swiper
      const heroSwiper = new Swiper('.heroSwiper', {
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
    });
    </script>

    {{-- ===== Activities Section (Tailwind + Alpine modal) ===== --}}
    <main id="main">
      <section id="set-menu" class="container mx-auto px-4 py-12">
        <div class="max-w-6xl mx-auto">
          <h2 class="font-extrabold uppercase leading-tight tracking-wide mb-6 text-left" style=" color:#a97464; font-size: 25px;">
            Enhance your stay with our special events.
          </h2>

          {{-- Two-panel Activities Carousel --}}
          <div id="activities-carousel" class="relative" aria-roledescription="carousel" x-data
               x-init="$nextTick(() => window.__initActivitiesCarousel && window.__initActivitiesCarousel())">
            {{-- Stage --}}
            <div class="relative bg-[#f0e8df] rounded-2xl p-2 overflow-hidden">
              {{-- Arrows --}}
              <button type="button" aria-label="Previous slide"
                      class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-[rgba(169,116,100,.9)] text-white shadow"
                      x-on:click="window.__activitiesNav && window.__activitiesNav(-1)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
              <button type="button" aria-label="Next slide"
                      class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-[rgba(169,116,100,.9)] text-white shadow"
                      x-on:click="window.__activitiesNav && window.__activitiesNav(1)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>

              {{-- Slide container (JS injects the .slide into this div) --}}
              <div class="min-h-80" id="activities-stage"></div>
            </div>

            {{-- Dots --}}
            <div id="activities-dots" class="flex gap-2 justify-center py-3" role="tablist" aria-label="Slides"></div>

            {{-- Slide template (hidden) --}}
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
                    <span class="bg-[#a97464] text-white font-extrabold text-[13px] tracking-[.08em] uppercase rounded-[10px] py-2.25 px-3 leading-none cursor-pointer"></span>
                    <a class="text-white text-[14px] tracking-[.08em] uppercase py-2.25 px-3 no-underline hover:underline cursor-pointer">Learn more >></a>
                  </div>
                </article>

                {{-- Right panel (removed for odd count) --}}
                <article class="panel right-panel relative overflow-hidden rounded-none shadow-none">
                  <img alt=""
                       class="w-full block object-cover"
                       style="height: 520px; transform: scale(1); transition: transform .5s ease;">
                  <div class="absolute inset-0 pointer-events-none"
                       style="background: linear-gradient(180deg, rgba(0,0,0,0) 50%, rgba(0,0,0,.35) 85%);"></div>
                  <div class="absolute left-3 bottom-3 flex flex-col items-start gap-1">
                    <span class="bg-[#a97464] text-white font-extrabold text-[13px] tracking-[.08em] uppercase rounded-[10px] py-2.25 px-3 leading-none cursor-pointer"></span>
                    <a class="text-white text-[14px] tracking-[.08em] uppercase py-2.25 px-3 no-underline hover:underline cursor-pointer">Learn more >></a>
                  </div>
                </article>
              </div>
            </template>
          </div>
        </div>
      </section>
    </main>
    {{-- ===== End Activities Section ===== --}}

    {{-- Build the activities data for JS --}}
    @php
      $activities = [
        'Team Building' => [
          'title' => 'Team Building',
          'description' => 'Activities for group goals and bonding.',
          'images' => [
            asset('assets/images/carousel/Teambuilding2.jpg'),
            asset('assets/images/carousel/Teambuilding9.jpg'),
            asset('assets/images/carousel/games-grounds4.jpg'),
          ],
          'details' => [
            'Icebreaker Games',
            'Trust Exercises',
            'Outdoor Challenges',
            'Problem Solving Activities'
          ]
        ],
        'Family Camps' => [
          'title' => 'Family Camps',
          'description' => 'Fun bonding experiences for families.',
          'images' => [
            asset('assets/images/carousel/bonfire.jpg'),
            asset('assets/images/carousel/bonfire-5.jpg'),
            asset('assets/images/carousel/OUTDOOR MOVIE.jpg'),
          ],
          'details' => [
            'Picnic Setups',
            'Family Games',
            'Nature Walks',
            'Campfire Stories'
          ]
        ],
        'Retreats' => [
          'title' => 'Retreats',
          'description' => 'Wellness, spiritual, or work-focused.',
          'images' => [
            asset('assets/images/carousel/Kawa bath.jpg'),
            asset('assets/images/carousel/animal and wellness.jpg'),
            asset('assets/images/carousel/Retreat-705.jpg'),
          ],
          'details' => [
            'Yoga Sessions',
            'Meditation Workshops',
            'Team Reflection Activities',
            'Wellness Meals'
          ]
        ],
        'Adventure Activities' => [
          'title' => 'Adventure Activities',
          'description' => 'Exciting outdoor group adventures.',
          'images' => [
            asset('assets/images/carousel/archery.jpg'),
            asset('assets/images/carousel/atv.jpg'),
            asset('assets/images/carousel/bikes.jpg'),
          ],
          'details' => [
            'White Water Rafting',
            'Ropes Course',
            'Zipline',
            'Obstacle Course'
          ]
        ],
        'Inclusion & Program Description' => [
          'title' => 'Inclusion & Program Description',
          'description' => 'See what\'s included and program details for your group booking.',
          'images' => [
            asset('assets/images/carousel/setmenu1.jpg'),
            asset('assets/images/carousel/menu1.jpg'),
            asset('assets/images/carousel/menu2.jpg'),
          ],
          'details' => [
            'Inclusions for all group packages',
            'Sample program flow',
            'Customizable options available'
          ]
        ]
      ];

      // Pair activities (2 per slide)
      $pairs = array_chunk($activities, 2, true);
      $slidesForJs = [];
      foreach ($pairs as $pair) {
        $values = array_values($pair);
        $left  = $values[0] ?? null;
        $right = $values[1] ?? null;

        $slide = [];
        if ($left) {
          $slide['left'] = [
            'title'   => $left['title'],
            'img'     => $left['images'][0],
            'alt'     => $left['title'],
            'details' => $left['details'],
            'images'  => $left['images'],
          ];
        }
        if ($right) {
          $slide['right'] = [
            'title'   => $right['title'],
            'img'     => $right['images'][0],
            'alt'     => $right['title'],
            'details' => $right['details'],
            'images'  => $right['images'],
          ];
        }
        $slidesForJs[] = $slide;
      }
    @endphp

    {{-- Alpine-powered Details Modal --}}
    <div x-data="{ open:false, modal:{ title:'', details:[], images:[] } }"
         x-on:open-activity.window="modal=$event.detail; open=true"
         class="relative z-50">
      <div x-show="open" class="fixed inset-0 bg-transparent" x-transition.opacity></div>
      <div x-show="open"
           x-transition
           class="fixed inset-0 flex items-center justify-center p-4"
           style="display: none;">
        <div class="bg-white rounded-xl w-full max-w-3xl shadow-xl">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200">
          <h5 class="font-semibold text-lg" x-text="modal.title"></h5>
          <button @click="open=false" class="p-2 hover:bg-gray-100 rounded">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

          <div class="p-5 space-y-4">
            {{-- Simple image carousel --}}
            <div x-data="{i:0}" class="relative">
              <template x-if="modal.images && modal.images.length">+
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
                              :class="idx===i ? 'bg-[#a97464] w-4' : 'bg-[#c8a89f]'"
                              @click="i=idx"></button>
                    </template>
                  </div>
                </div>
              </template>
            </div>

            {{-- Details list --}}
            <div class="space-y-2">
              <template x-for="(p, idx) in modal.details" :key="idx">
                <p style="font-size: 13px;">
                  <i class="bi bi-check-circle-fill mr-2" style="color:#a97464;"></i>
                  <span x-text="p"></span>
                </p>
              </template>
            </div>
          </div>

          <div class="px-5 py-4 border-t flex flex-col md:flex-row items-center justify-end gap-3">
            <a href="{{ url('/booking') }}"
               class="inline-flex items-center justify-center px-6 py-3 rounded-2xl text-white font-semibold hover:opacity-90 transition-colors duration-300 w-full md:w-auto">
              Book Now
            </a>
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
    {{-- Activities Carousel Data --}}
    <script type="application/json" id="slides-data">
      {!! json_encode($slidesForJs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    {{-- Activities Carousel JS --}}
    <script>
      const slides = JSON.parse(document.getElementById('slides-data').textContent);

      // open details helper (dispatches to Alpine modal)
      function viewDetails(title, details = [], images = []) {
        window.dispatchEvent(new CustomEvent('open-activity', {
          detail: { title, details, images }
        }));
      }

      (function(){
        const root  = document.getElementById('activities-carousel');
        const stage = document.getElementById('activities-stage');
        const dotsEl= document.getElementById('activities-dots');
        const tpl   = document.getElementById('slide-template');

        let index = 0;
        const count = slides.length;

        function open(item){
          viewDetails(item.title, item.details || [], item.images || []);
        }

        function wire(panelEl, item){
          const img = panelEl.querySelector('img');
          const chip= panelEl.querySelector('span');
          const link= panelEl.querySelector('a');

          img.src = item.img;
          img.alt = item.alt || item.title;
          img.style.cursor = 'pointer';
          chip.textContent = item.title;

          [img, chip, link].forEach(el => {
            el && el.addEventListener('click', (e)=>{ e.preventDefault(); open(item); });
          });
        }

        function render(i){
          stage.querySelectorAll('.slide').forEach(n => n.remove());
          const frag = tpl.content.cloneNode(true);
          const panels = frag.querySelectorAll('.panel');

          const L = slides[i].left;
          if (L){ wire(panels[0], L); }

          const R = slides[i].right;
          if (R){ wire(panels[1], R); }
          else { panels[1].remove(); }

          const slideEl = frag.querySelector('.slide');
          slideEl.classList.add('sm:grid-cols-2');

          stage.appendChild(frag);
          updateDots(i);
        }

        function updateDots(active){
          dotsEl.innerHTML = '';
          for(let i=0;i<count;i++){
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'h-2.5 rounded-full ' + (i===active ? 'bg-[#a97464] w-4' : 'bg-[#c8a89f] w-2.5');
            b.setAttribute('role','tab');
            b.setAttribute('aria-label', `Go to slide ${i+1}`);
            b.addEventListener('click', ()=>goTo(i));
            dotsEl.appendChild(b);
          }
        }

        function animate(delta){
          const old = stage.querySelector('.slide');
          if(!old){ render(index); return; }
          old.style.opacity = '0';
          old.style.transform = `translateX(${delta>0?-24:24}px)`;
          setTimeout(()=> render(index), 180);
        }

        function go(delta){ index = (index + delta + count) % count; animate(delta); }
        function goTo(i){ const d = i > index ? 1 : -1; index = i; animate(d); }

        // expose controls
        window.__activitiesNav = go;
        window.__initActivitiesCarousel = ()=> render(index);

        // keyboard nav
        root.setAttribute('tabindex','0');
        root.addEventListener('keydown', (e)=> {
          if(e.key === 'ArrowLeft')  go(-1);
          if(e.key === 'ArrowRight') go(1);
        });

        // swipe
        let startX = null;
        stage.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, {passive:true});
        stage.addEventListener('touchend', e => {
          if(startX==null) return;
          const dx = e.changedTouches[0].clientX - startX;
          if(dx > 40) go(-1);
          if(dx < -40) go(1);
          startX = null;
        }, {passive:true});
      })();
    </script>

    {{-- Footer --}}
    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>