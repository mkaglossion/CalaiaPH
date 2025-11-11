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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  </head>
  <body class="bg-white-100">
      <div class="flex flex-col items-center justify-center px-4">
        @if (session('success'))
            <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
                {{ session('success') }}
            </div>
        @endif
      </div>

    <x-header active="activities" />

    {{-- Hero Slider --}}
    <div class="swiper heroSwiper w-full h-64 sm:h-80 md:h-96 lg:h-[500px] xl:h-[600px]">
      <div class="swiper-wrapper">
        @forelse ($swiperImages as $image)
          <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
            <img 
              src="{{ asset('storage/' . $image->image_path) }}" 
              alt="{{ $image->title ?? 'Slide Image' }}" 
              class="w-full h-full object-cover object-center" 
              loading="lazy"
            >
            <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
            <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
              <div class="text-center px-4">
                @if($image->title)
                  <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4 uppercase" 
                      style="font-family: gotham; font-size: 3.5rem;">
                    {{ $image->title }}
                  </h2>
                @endif
                <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase"
                  style="font-family: 'Vilaka Modern Serif', serif; font-style: italic; font-size: 1.2rem;">
                  Experience the beauty of nature
                </p>
              </div>
            </div>
          </div>
        @empty
          <div class="swiper-slide flex items-center justify-center bg-gray-200 text-gray-600">
            <p>No images available</p>
          </div>
        @endforelse
      </div>
    </div>

    {{-- ===== Activities Section (Tailwind + Alpine modal) ===== --}}
    <main id="main">
      <section id="adventure" class="container mx-auto px-4 py-12">
        <div class="max-w-[1200px] mx-auto">
          <h2 class="flex items-start gap-3 font-extrabold uppercase leading-tight tracking-wide mb-6"
              style="font-family: Gotham, sans-serif; color:#a97464; font-size: clamp(20px, 2.1vw + 14px, 32px);">
            <span class="w-2 rounded-sm bg-[#a97464] mt-1" aria-hidden="true"></span>
            <span class="block">Enhance your stay with our fun-filled activities.</span>
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
              <div class="min-h-[320px]" id="activities-stage"></div>
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
                    <span class="bg-[#a97464] text-white font-extrabold text-[13px] tracking-[.08em] uppercase rounded-[10px] py-[9px] px-3 leading-none cursor-pointer"></span>
                    <a class="text-white/90 text-[15px] tracking-[.08em] uppercase no-underline hover:underline cursor-pointer">Learn more >></a>
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
                    <span class="bg-[#a97464] text-white font-extrabold text-[13px] tracking-[.08em] uppercase rounded-[10px] py-[9px] px-3 leading-none cursor-pointer"></span>
                    <a class="text-white/90 text-[15px] tracking-[.08em] uppercase no-underline hover:underline cursor-pointer">Learn more >></a>
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
        'Horseback Riding' => [
          'title' => 'Horseback Riding',
          'description' => 'Enjoy scenic horseback rides and nature exploration.',
          'images' => [
            asset('assets/images/carousel/our-farm2.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
          ],
          'details' => [
            'Price: ₱600 per 30 min',
            'About: Relaxing guided rides perfect for beginners and experienced riders. (temp)',
            'Approximate trail length: ~1 km around Purok 3, Brgy. Bisaya, Calaca (temp)',
            'Includes basic riding lessons and safety briefing. (temp)',
            'Opportunity to interact with well-trained horses. (temp)',
            'Great for families and small groups. (temp)',
          ]
        ],
        'Mini Kalesa' => [
          'title' => 'Mini Kalesa (up to 160 total lbs)',
          'description' => 'Charming mini carriage ride for kids and light adults. (temp)',
          'images' => [
            asset('assets/images/carousel/slides CALAIA-01.png'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
          ],
          'details' => [
            'Price: ₱600 per 30 min',
            'About: Traditional Filipino-style mini carriage pulled by a gentle pony. (temp)',
            'Approximate ride loop: ~500 meters around the garden area. (temp)',
            'Weight limit: up to 160 lbs total (ideal for kids or 1 adult + 1 child). (temp)',
            'Includes quick photo session with the kalesa. (temp)',
            'Perfect for themed events and family fun. (temp)',
          ]
        ],
        'ATV' => [
          'title' => 'ATV',
          'description' => 'Exciting off-road ATV ride perfect for thrill seekers. (temp)',
          'images' => [
            asset('assets/images/carousel/atv.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
          ],
          'details' => [
            'Price: ₱600 per 30 min',
            'About: Powerful all-terrain vehicles for guided adventure rides. (temp)',
            'Approximate trail length: ~1.5 km of mixed terrain. (temp)',
            'Includes helmet and quick safety briefing. (temp)',
            'Ideal for beginners and experienced riders. (temp)',
          ]
        ],
        'Go Kart' => [
          'title' => 'Go Kart',
          'description' => 'Fun go kart ride on a safe and exciting track. (temp)',
          'images' => [
            asset('assets/images/carousel/go-cart2.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
          ],
          'details' => [
            'Price: ₱800 per 20 min',
            'About: Single-seat go karts perfect for beginners and enthusiasts. (temp)',
            'Approximate track length: ~400 meters loop. (temp)',
            'Includes helmet and safety briefing. (temp)',
            'Great for solo rides or friendly races. (temp)',
          ]
        ],
        'Inflatable Slide' => [
          'title' => 'Inflatable Slide',
          'description' => 'Cool water slide fun and thrilling zipline ride. (temp)',
          'images' => [
            asset('assets/images/carousel/slide-n-go2.jpg'),
            asset('assets/images/carousel/zipline-1.jpg'),
            asset('assets/images/carousel/slip-slide-2.jpg'),
          ],
          'details' => [
            'Price: ₱1000 per hour',
            'About: Combo access to slip and slide water ride and scenic zipline. (temp)',
            'Approximate zipline length: ~200 meters. (temp)',
            'Includes basic safety gear and staff assistance. (temp)',
            'Perfect for cooling off and adrenaline fun. (temp)',
          ]
        ],
        'Playground' => [
          'title' => 'Playground',
          'description' => 'Open play area for kids. (temp)',
          'images' => [
            asset('assets/images/carousel/slides CALAIA-01.png'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: Free',
            'About: Swings, slides, and climbing frames. (temp)',
            'Safe and family-friendly. (temp)',
          ]
        ],
        'Fishing' => [
          'title' => 'Fishing',
          'description' => 'Relaxing catch-and-release pond fishing. (temp)',
          'images' => [
            asset('assets/images/carousel/slides CALAIA-01.png'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: ₱200 per rod / 2 hours',
            'About: Quiet fishing spot for guests. (temp)',
            'Basic rods available. (temp)',
          ]
        ],
        'Farm Tour / Animal Feeding' => [
          'title' => 'Farm Tour / Animal Feeding',
          'description' => 'Feed friendly animals and explore the farm. (temp)',
          'images' => [
            asset('assets/images/carousel/Retreat-880.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: ₱250',
            'About: Hand-feed goats, rabbits, etc. (temp)',
            'Great for kids and families. (temp)',
          ]
        ],
        'Disc Golf' => [
          'title' => 'Disc Golf',
          'description' => 'Play disc golf around scenic course. (temp)',
          'images' => [
            asset('assets/images/carousel/slides CALAIA-01.png'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: ₱250 per game',
            'About: Throw discs into metal baskets over several holes. (temp)',
            'Includes discs rental. (temp)',
          ]
        ],
        'Archery Tag' => [
          'title' => 'Archery Tag',
          'description' => 'Fun team archery game with foam-tipped arrows. (temp)',
          'images' => [
            asset('assets/images/carousel/archery.jpg'),
            asset('assets/images/carousel/Calaia-26.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
          ],
          'details' => [
            'Price: ₱250 per person / 50 min',
            'About: Safe archery-based team sport. (temp)',
            'Includes equipment and mask. (temp)',
          ]
        ],
        'Jumbo Badminton' => [
          'title' => 'Jumbo Badminton',
          'description' => 'Play badminton with oversized rackets. (temp)',
          'images' => [
            asset('assets/images/carousel/games-grounds4.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: Free',
            'About: Fun, casual outdoor game. (temp)',
          ]
        ],
        'Sandy Pickleball' => [
          'title' => 'Sandy Pickleball',
          'description' => 'Casual pickleball game on sand court. (temp)',
          'images' => [
            asset('assets/images/carousel/games-grounds4.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: Free',
            'About: Combines tennis and ping pong. (temp)',
          ]
        ],
        'Outdoor Movie Night' => [
          'title' => 'Outdoor Movie Night',
          'description' => 'Watch movies under the stars. (temp)',
          'images' => [
            asset('assets/images/carousel/OUTDOOR MOVIE.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: ₱1500 (Strictly 7 to 9 PM only)',
            'About: Evening screening on projector with beanbags. (temp)',
            'Schedule varies – see reception. (temp)',
          ]
        ],
        'Tree Planting' => [
          'title' => 'Tree Planting',
          'description' => 'Help plant native trees on site. (temp)',
          'images' => [
            asset('assets/images/carousel/Retreat-1164.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: ₱120',
            'About: Contribute to reforestation. (temp)',
            'Includes seedling and certificate. (temp)',
          ]
        ],
        'Corn Hole' => [
          'title' => 'Corn Hole',
          'description' => 'Classic bean bag tossing game. (temp)',
          'images' => [
            asset('assets/images/carousel/corn hole.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: Free',
            'About: Casual family game. (temp)',
          ]
        ],
        'Hiking' => [
          'title' => 'Hiking',
          'description' => 'Walk scenic nature trails on site. (temp)',
          'images' => [
            asset('assets/images/carousel/slides CALAIA-01.png'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: Free',
            'About: Self-guided hike around property. (temp)',
          ]
        ],
        'Spikeball' => [
          'title' => 'Spikeball',
          'description' => 'Fast-paced ball game with net. (temp)',
          'images' => [
            asset('assets/images/carousel/spikeball.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: Free',
            'About: 2 vs 2 sport on small round net. (temp)',
          ]
        ],
        'Darts' => [
          'title' => 'Darts',
          'description' => 'Play darts in recreation area. (temp)',
          'images' => [
            asset('assets/images/carousel/slides CALAIA-01.png'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: ₱250 per 30 min',
            'About: Traditional dartboard. (temp)',
          ]
        ],
        'Foosball' => [
          'title' => 'Foosball',
          'description' => 'Table soccer game with rods. (temp)',
          'images' => [
            asset('assets/images/carousel/slides CALAIA-01.png'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: ₱250 per 30 min',
            'About: Play against friends. (temp)',
          ]
        ],
        'Board Games / Cards' => [
          'title' => 'Board Games / Cards',
          'description' => 'Play classic games indoors. (temp)',
          'images' => [
            asset('assets/images/carousel/slides CALAIA-01.png'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: Free',
            'About: Selection of board and card games. (temp)',
          ]
        ],
        'Arts and Crafts' => [
          'title' => 'Arts and Crafts',
          'description' => 'DIY craft corner for kids and adults. (temp)',
          'images' => [
            asset('assets/images/carousel/slides CALAIA-01.png'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: Free',
            'About: Use paper, paint, and materials. (temp)',
          ]
        ],
        'Mountain Bike' => [
          'title' => 'Mountain Bike',
          'description' => 'Ride bikes around the property. (temp)',
          'images' => [
            asset('assets/images/carousel/Retreat-900.jpg'),
            asset('assets/images/carousel/bikes.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
          ],
          'details' => [
            'Price: ₱250 per hour',
            'About: Rental bikes available. (temp)',
          ]
        ],
        'Kite Flying' => [
          'title' => 'Kite Flying',
          'description' => 'Fly kites in open field. (temp)',
          'images' => [
            asset('assets/images/carousel/slides CALAIA-01.png'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: Free',
            'About: Bring your own or borrow basic kite. (temp)',
          ]
        ],
        'Kawa Bath' => [
          'title' => 'Kawa Bath',
          'description' => 'Soak in warm giant wok bath. (temp)',
          'images' => [
            asset('assets/images/carousel/Kawa bath.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
            asset('assets/images/carousel/slide-n-go2.jpg'),
          ],
          'details' => [
            'Price: ₱1000 per hour',
            'About: Relaxing warm soak in outdoor kawa. (temp)',
          ]
        ],
        'Smores Bonfire Experience' => [
          'title' => 'Smores Bonfire Experience',
          'description' => 'Make smores by the fire. (temp)',
          'images' => [
            asset('assets/images/carousel/bonfire.jpg'),
            asset('assets/images/carousel/Retreat-705.jpg'),
            asset('assets/images/carousel/activities-1.jpg'),
          ],
          'details' => [
            'Price: ₱500',
            'About: Includes marshmallows, chocolate, and crackers. (temp)',
          ]
        ],
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
      <div x-show="open" class="fixed inset-0 bg-black/50" x-transition.opacity></div>
      <div x-show="open"
           x-transition
           class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl w-full max-w-3xl shadow-xl">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold" x-text="modal.title + ' Details'"></h3>
            <button class="p-2 hover:bg-gray-100 rounded-md" @click="open=false" aria-label="Close">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto">
            {{-- Simple image carousel --}}
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

            {{-- Details list --}}
            <div class="space-y-2">
              <template x-for="(p, idx) in modal.details" :key="idx">
                <p class="text-[15px]">
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

    {{-- Swiper JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Initialize hero swiper
      const heroSwiper = new Swiper('.heroSwiper', {
        autoplay: {
          delay: 3000,
          disableOnInteraction: false,
        },
        loop: true,
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

    {{-- Alpine (needed for modal) --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>
