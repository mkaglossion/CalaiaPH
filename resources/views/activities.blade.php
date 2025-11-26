<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Discover thrilling outdoor activities at CalaiaPH. From biking trails to adventure sports, experience the best of nature-based recreation and team-building activities.">
    <meta name="keywords" content="outdoor activities, biking trails, adventure sports, team building, nature recreation, CalaiaPH, Philippines activities">
    <meta name="author" content="CalaiaPH">
    @vite(['resources/css/app.css'])
    <title>CalaiaPH - Activities</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  </head>
  <body class="bg-[#f5efe7]">
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
        <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
            {{ session('success') }}
        </div>
      @endif
    </div>

    <x-header active="activities" />

    {{-- Hero Slider --}}
    <div class="swiper heroSwiper w-full h-64 sm:h-80 md:h-96 lg:h-[500px] xl:h-[600px]" data-slides-count="{{ count($swiperImages) }}">
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
    </div>

    {{-- ========= ACTIVITIES SECTION ========= --}}
    <main id="main">
      <section class="container mx-auto px-4 py-12">
        <div class="max-w-[1200px] mx-auto">
          <h2 class="font-extrabold uppercase leading-tight tracking-wide mb-10 text-left" style="font-family: 'Gotham Book', sans-serif; color:#a97464; font-size: 25px;">
            Enhance your stay with our fun-filled activities.
          </h2>

          <div class="space-y-12">

            {{-- ADVENTURE --}}
            <div id="adventure">
              <h3 class="font-bold uppercase tracking-wide mb-4" style="font-family: 'Gotham Book', sans-serif; color:#a97464; font-size: 25px;">
                Adventure
              </h3>

              <div id="adventure-carousel" class="relative" aria-roledescription="carousel">
                <div class="relative bg-[#f0e8df] rounded-2xl p-2 overflow-hidden">
                  <button type="button" aria-label="Previous slide"
                          data-role="prev"
                          class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-[rgba(169,116,100,.9)] text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide"
                          data-role="next"
                          class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-[rgba(169,116,100,.9)] text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>

                  <div class="min-h-[320px]" id="adventure-stage"></div>
                </div>
                <div id="adventure-dots" class="flex gap-2 justify-center py-3" role="tablist" aria-label="Adventure slides"></div>
              </div>
            </div>

            {{-- SPORTS AND GAMES --}}
            <div id="sports-games">
              <h3 class="font-bold uppercase tracking-wide mb-4" style="font-family: 'Gotham Book', sans-serif; color:#a97464; font-size: 25px;">
                Sports &amp; Games
              </h3>

              <div id="sports-carousel" class="relative" aria-roledescription="carousel">
                <div class="relative bg-[#f0e8df] rounded-2xl p-2 overflow-hidden">
                  <button type="button" aria-label="Previous slide"
                          data-role="prev"
                          class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-[rgba(169,116,100,.9)] text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide"
                          data-role="next"
                          class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-[rgba(169,116,100,.9)] text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>

                  <div class="min-h-[320px]" id="sports-stage"></div>
                </div>
                <div id="sports-dots" class="flex gap-2 justify-center py-3" role="tablist" aria-label="Sports &amp; games slides"></div>
              </div>
            </div>

            {{-- R & R + CAMP --}}
            <div id="rr-camp">
              <h3 class="font-bold uppercase tracking-wide mb-4" style="font-family: 'Gotham Book', sans-serif; color:#a97464; font-size: 25px;">
                R &amp; R &amp; Camp
              </h3>

              <div id="rrcamp-carousel" class="relative" aria-roledescription="carousel">
                <div class="relative bg-[#f0e8df] rounded-2xl p-2 overflow-hidden">
                  <button type="button" aria-label="Previous slide"
                          data-role="prev"
                          class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-[rgba(169,116,100,.9)] text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide"
                          data-role="next"
                          class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-[rgba(169,116,100,.9)] text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>

                  <div class="min-h-[320px]" id="rrcamp-stage"></div>
                </div>
                <div id="rrcamp-dots" class="flex gap-2 justify-center py-3" role="tablist" aria-label="R &amp; R &amp; Camp slides"></div>
              </div>
            </div>

            {{-- FARM --}}
            <div id="farm">
              <h3 class="font-bold uppercase tracking-wide mb-4" style="font-family: 'Gotham Book', sans-serif; color:#a97464; font-size: 25px;">
                Farm
              </h3>

              <div id="farm-carousel" class="relative" aria-roledescription="carousel">
                <div class="relative bg-[#f0e8df] rounded-2xl p-2 overflow-hidden">
                  <button type="button" aria-label="Previous slide"
                          data-role="prev"
                          class="absolute top-1/2 -translate-y-1/2 left-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-[rgba(169,116,100,.9)] text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M15 6l-6 6 6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                  <button type="button" aria-label="Next slide"
                          data-role="next"
                          class="absolute top-1/2 -translate-y-1/2 right-2 z-10 grid place-items-center w-10 h-10 rounded-full bg-[rgba(169,116,100,.9)] text-white shadow">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M9 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>

                  <div class="min-h-[320px]" id="farm-stage"></div>
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
                  <span class="bg-[#a97464] text-white font-extrabold text-[13px] tracking-[.08em] uppercase rounded-[10px] py-[9px] px-3 leading-none cursor-pointer"></span>
                  <a class="text-white text-[14px] tracking-[.08em] uppercase py-[9px] px-3 no-underline hover:underline cursor-pointer">Learn more >></a>
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
                  <span class="bg-[#a97464] text-white font-extrabold text-[13px] tracking-[.08em] uppercase rounded-[10px] py-[9px] px-3 leading-none cursor-pointer"></span>
                  <a class="text-white text-[14px] tracking-[.08em] uppercase py-[9px] px-3 no-underline hover:underline cursor-pointer">Learn more >></a>
                </div>
              </article>
            </div>
          </template>
        </div>
      </section>
    </main>
    {{-- ========= END ACTIVITIES SECTION ========= --}}

    {{-- ===== Build activities data (grouped by category) ===== --}}
    @php
      $activitiesByCategory = [
        'adventure' => [
          [
            'title' => 'Go-Kart (15yo above)',
            'images' => [
              asset('assets/images/carousel/go-cart2.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
            ],
            'details' => [
              '4 rounds / 10 minutes (approx.)',
              'Rate: ₱500 per session',
              'Age requirement: 15 years old and above',
            ],
          ],
          [
            'title' => 'ATV (15yo above)',
            'images' => [
              asset('assets/images/carousel/atv.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
            ],
            'details' => [
              'Duration: 20 minutes',
              'Rate: ₱800',
              'Age requirement: 15 years old and above',
            ],
          ],
          [
            'title' => 'Wet Inflatable Slide',
            'images' => [
              asset('assets/images/carousel/slide-n-go2.jpg'),
              asset('assets/images/carousel/slip-slide-2.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
            ],
            'details' => [
              'Rate: ₱350 per child per hour',
              'Minimum of 3 children or ₱1,050',
            ],
          ],
          [
            'title' => 'Mini Kalesa',
            'images' => [
              asset('assets/images/carousel/minikalesa.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Duration: 30 minutes',
              'Rate: ₱600',
              'Weight limit: 200 lbs max',
            ],
          ],
          [
            'title' => 'Horseback Riding',
            'images' => [
              asset('assets/images/carousel/our-farm2.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Duration: 30 minutes',
              'Rate: ₱600',
            ],
          ],
        ],

        'sports' => [
          [
            'title' => 'Mountain Bike',
            'images' => [
              asset('assets/images/carousel/Retreat-900.jpg'),
              asset('assets/images/carousel/bikes.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
            ],
            'details' => [
              'Duration: 1 hour',
              'Rate: ₱300',
            ],
          ],
          [
            'title' => 'Archery Tag | Target',
            'images' => [
              asset('assets/images/carousel/archery.jpg'),
              asset('assets/images/carousel/Calaia-26.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
            ],
            'details' => [
              'Duration: 45 minutes',
              'Rate: ₱150',
            ],
          ],
          [
            'title' => 'Disc Golf',
            'images' => [
              asset('assets/images/carousel/slides CALAIA-01.png'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Rate: ₱250 per game',
            ],
          ],
          [
            'title' => 'Kiteflying',
            'images' => [
              asset('assets/images/carousel/slides CALAIA-01.png'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Duration: 1 hour',
              'Rate: ₱150',
            ],
          ],
          [
            'title' => 'Board Games / Cards',
            'images' => [
              asset('assets/images/carousel/slides CALAIA-01.png'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Casual indoor board and card games.',
            ],
          ],
          [
            'title' => 'Sandy Pickle',
            'images' => [
              asset('assets/images/carousel/games-grounds4.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Sand pickleball-style game.',
            ],
          ],
          [
            'title' => 'Frisbee',
            'images' => [
              asset('assets/images/carousel/games-grounds4.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Casual throwing game in the field.',
            ],
          ],
          [
            'title' => 'Field Volleyball',
            'images' => [
              asset('assets/images/carousel/games-grounds4.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Outdoor volleyball on the grass field.',
            ],
          ],
        ],

        'rrcamp' => [
          [
            'title' => 'Kawa Bath',
            'images' => [
              asset('assets/images/carousel/Kawa bath.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Duration: 1 hour (1–4 persons)',
              'Rate: ₱1,000 hot / ₱2,000 cold',
            ],
          ],
          [
            'title' => 'Bonfire & S’mores',
            'images' => [
              asset('assets/images/carousel/bonfire.jpg'),
              asset('assets/images/carousel/Retreat-705.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
            ],
            'details' => [
              '₱350 bonfire',
              '₱450 s’mores (25 pcs)',
            ],
          ],
          [
            'title' => 'Hiking',
            'images' => [
              asset('assets/images/carousel/slides CALAIA-01.png'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Enjoy scenic nature trails around the property.',
            ],
          ],
          [
            'title' => 'Fishing',
            'images' => [
              asset('assets/images/carousel/OUTDOOR MOVIE.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              '1 hour with cup of bait',
              '₱250 per rod',
              'Appropriately-sized catch sold at market price',
            ],
          ],
          [
            'title' => 'Outdoor Movie',
            'images' => [
              asset('assets/images/carousel/OUTDOOR MOVIE.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Strictly 6–9 PM only',
              'Rate: ₱1,500',
            ],
          ],
        ],

        'farm' => [
          [
            'title' => 'Tree Planting',
            'images' => [
              asset('assets/images/carousel/Retreat-1164.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              '₱200 per tree',
              'Includes name label on the tree',
            ],
          ],
          [
            'title' => 'Farm Tour / Animal Feeding',
            'images' => [
              asset('assets/images/carousel/Retreat-880.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              '₱150 per person',
              'Guided tour with friendly farm animals',
            ],
          ],
          [
            'title' => 'Veggie Picking',
            'images' => [
              asset('assets/images/carousel/Retreat-880.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Prices based on market price that day',
              'Pick fresh vegetables straight from the farm',
            ],
          ],
          [
            'title' => 'Animal Encounters',
            'images' => [
              asset('assets/images/carousel/our-farm2.jpg'),
              asset('assets/images/carousel/activities-1.jpg'),
              asset('assets/images/carousel/slide-n-go2.jpg'),
            ],
            'details' => [
              'Close-up interactions with farm animals',
              'Perfect for kids and families',
            ],
          ],
        ],
      ];

      // Pair activities (2 per slide) for each category
      $slidesByCategory = [];

      foreach ($activitiesByCategory as $key => $activities) {
        $pairs = array_chunk($activities, 2);
        $slides = [];

        foreach ($pairs as $pair) {
          $left  = $pair[0] ?? null;
          $right = $pair[1] ?? null;

          $slide = [];
          if ($left) {
            $slide['left'] = [
              'title'   => $left['title'],
              'img'     => $left['images'][0] ?? '',
              'alt'     => $left['title'],
              'details' => $left['details'] ?? [],
              'images'  => $left['images'] ?? [],
            ];
          }
          if ($right) {
            $slide['right'] = [
              'title'   => $right['title'],
              'img'     => $right['images'][0] ?? '',
              'alt'     => $right['title'],
              'details' => $right['details'] ?? [],
              'images'  => $right['images'] ?? [],
            ];
          }

          $slides[] = $slide;
        }

        $slidesByCategory[$key] = $slides;
      }
    @endphp

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

    {{-- Alpine-powered Details Modal (unchanged) --}}
    <div x-data="{ open:false, modal:{ title:'', details:[], images:[] } }"
         x-on:open-activity.window="modal=$event.detail; open=true"
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
                       class="w-full h-[300px] object-cover rounded-lg">
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

            <div class="space-y-2">
              <template x-for="(p, idx) in modal.details" :key="idx">
                <p style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                  <i class="fa-solid fa-circle-check text-[#a97464] mr-2"></i>
                  <span x-text="p"></span>
                </p>
              </template>
            </div>
          </div>

          <div class="px-5 py-4 border-t flex flex-col md:flex-row items-center justify-end gap-3">
            <a href="{{ url('/booking') }}"
               class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-[#a97464] text-white font-semibold hover:opacity-90 transition-colors duration-300 w-full md:w-auto">
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

    {{-- Swiper JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    {{-- Activities + Hero JS --}}
    <script>
      // Global helper for opening modal
      function viewDetails(title, details = [], images = []) {
        window.dispatchEvent(new CustomEvent('open-activity', {
          detail: { title, details, images }
        }));
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
          viewDetails(item.title, item.details || [], item.images || []);
        }

        function wire(panelEl, item) {
          const img  = panelEl.querySelector('img');
          const chip = panelEl.querySelector('span');
          const link = panelEl.querySelector('a');

          img.src = item.img;
          img.alt = item.alt || item.title;
          img.style.cursor = 'pointer';
          chip.textContent = item.title;

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
            b.className = 'h-2.5 rounded-full ' + (i === active ? 'bg-[#a97464] w-4' : 'bg-[#c8a89f] w-2.5');
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
    </script>

    {{-- Footer --}}
    <x-footer />

    {{-- Alpine (needed for modal) --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>
