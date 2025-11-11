<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Visit our beautiful farm at CalaiaPH. Learn about sustainable farming, enjoy farm tours, and experience the source of our fresh, organic produce firsthand.">
    <meta name="keywords" content="organic farm, sustainable farming, farm tours, fresh produce, CalaiaPH farm, Philippines agriculture, farm visit">
    <meta name="author" content="CalaiaPH">
    @vite(['resources/css/app.css'])
    <title>CalaiaPH - Farm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  </head>
  <body class="bg-white-100">
    {{-- Flash --}}
    <div class="flex flex-col items-center justify-center px-4">
      @if (session('success'))
        <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
          {{ session('success') }}
        </div>
      @endif
    </div>

    <x-header active="farm" />

    {{-- Hero Slider (dining-style) --}}
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

    <main id="main">
      {{-- SECTION: Our Sustainable Farming --}}
      <section class="container mx-auto px-4 py-10">
        <div class="text-center mb-10">
          <h2 class="font-bold mb-2"
              style="color:#97655A; font-family:gotham, sans-serif; font-size:clamp(1.6rem,3vw,2.1rem);">
            Our Sustainable Farming
          </h2>
          <p class="mx-auto text-gray-700" style="max-width:760px;">
            Discover how we raise livestock and grow produce with respect for nature and animal welfare
          </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
          {{-- Left image --}}
          <img
            src="{{ asset('assets/images/carousel/animal and wellness.jpg') }}"
            alt="Free-range livestock"
            class="w-full h-[260px] sm:h-[320px] md:h-[360px] object-cover rounded-xl shadow"
          >
          {{-- Right text --}}
          <div class="lg:pl-6">
            <h3 class="mb-3 font-bold"
                style="color:#97655A; font-family:gotham, sans-serif; font-size:clamp(1.25rem,2.6vw,1.75rem);">
              Ethically Raised Livestock
            </h3>
            <p class="text-gray-700 mb-5">
              Our animals live as nature intended: with plenty of space to roam, fresh air, and a natural diet free from antibiotics and growth hormones.
            </p>
            <ul class="space-y-3">
              @foreach ([
                'Pasture-raised with daily access to open fields',
                'No antibiotics or hormones — ever',
                'Non-GMO, locally-sourced feed',
                'Humane treatment with low-stress handling',
              ] as $item)
              <li class="flex items-start">
                <span class="flex items-center justify-center rounded-full mr-3 mt-1"
                      style="width:24px;height:24px;background-color:rgba(151,101,90,.10);">
                  <i class="fas fa-check" style="color:#97655A; font-size:12px;"></i>
                </span>
                <span class="text-sm sm:text-base text-gray-700">{{ $item }}</span>
              </li>
              @endforeach
            </ul>
          </div>
        </div>

        {{-- Meat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
          @foreach ([
            ['img'=>'our-farm.jpg','title'=>'Grass-Fed Beef','desc'=>"100% grass-fed and grass-finished diet; higher in healthy Omega-3s."],
            ['img'=>'our-farm2.jpg','title'=>'Heritage Pork','desc'=>"Small groups with room to root and explore for superior flavor and texture."],
            ['img'=>'organic.png','title'=>'Pasture Lamb','desc'=>"Grazed on diverse pastures for naturally complex flavor."],
          ] as $c)
          <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
            <img src="{{ asset('assets/images/carousel/'.$c['img']) }}" alt="{{ $c['title'] }}"
                 class="w-full h-48 object-cover rounded-lg mb-4">
            <h4 class="font-semibold mb-1" style="color:#97655A;">{{ $c['title'] }}</h4>
            <p class="text-gray-700 mb-3">{{ $c['desc'] }}</p>
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium" style="color:#97655A;">Available seasonally</span>
              <i class="fas fa-leaf text-sm" style="color:#28a745;"></i>
            </div>
          </div>
          @endforeach
        </div>
      </section>

      {{-- SECTION: Fresh From Our Fields --}}
      <section class="py-12" style="background:#FFF;">
        <div class="container mx-auto px-4">
          <div class="text-center mb-10">
            <h2 class="font-bold mb-2"
                style="color:#97655A; font-family:gotham, sans-serif; font-size:clamp(1.6rem,3vw,2.1rem);">
              Fresh From Our Fields
            </h2>
            <p class="mx-auto text-gray-700" style="max-width:760px;">
              Seasonal, pesticide-free produce grown with care for flavor and nutrition.
            </p>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Vegetable Garden card --}}
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <img src="{{ asset('assets/images/carousel/organic1.jpg') }}" alt="Vegetable garden"
                     class="w-full h-40 md:h-full object-cover rounded-lg md:col-span-1">
                <div class="md:col-span-2">
                  <h3 class="font-semibold mb-1" style="color:#97655A;">Vegetable Garden</h3>
                  <p class="text-gray-700 mb-3">
                    We grow over 50 varieties of vegetables using organic methods and natural amendments like compost and cover crops.
                  </p>
                  <h4 class="font-semibold mb-1" style="color:#97655A;">In Season Now:</h4>
                  <p class="text-gray-700 mb-3">Heirloom tomatoes, rainbow chard, zucchini, garlic, kale, peppers, eggplant</p>
                  <a href="#contact" class="inline-flex items-center font-medium" style="color:#97655A;">
                    Get our harvest schedule <i class="fas fa-chevron-right text-xs ml-2"></i>
                  </a>
                </div>
              </div>
            </div>

            {{-- Orchard Fruits card --}}
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <img src="{{ asset('assets/images/carousel/Calaia-26.jpg') }}" alt="Orchard fruits"
                     class="w-full h-40 md:h-full object-cover rounded-lg md:col-span-1">
                <div class="md:col-span-2">
                  <h3 class="font-semibold mb-1" style="color:#97655A;">Orchard Fruits</h3>
                  <p class="text-gray-700 mb-3">
                    Our heritage fruit trees are grown without chemical sprays, allowing natural ecosystems to maintain balance.
                  </p>
                  <h4 class="font-semibold mb-1" style="color:#97655A;">Coming Soon:</h4>
                  <p class="text-gray-700 mb-3">Apples, pears, peaches, plums, cherries, persimmons</p>
                  <a href="#contact" class="inline-flex items-center font-medium" style="color:#97655A;">
                    Join our fruit CSA <i class="fas fa-chevron-right text-xs ml-2"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>

          {{-- Growing with Nature icons --}}
          <div class="bg-white rounded-xl shadow-sm p-6 md:p-8 border border-gray-100">
            <h3 class="text-center font-bold mb-6" style="color:#97655A;">Growing with Nature</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              @foreach ([
                ['icon'=>'bi-flower1','title'=>'Soil First','text'=>'We build our soil health through composting, cover crops, and minimal tillage.'],
                ['icon'=>'bi-tree-fill','title'=>'Biodiversity','text'=>'Companion planting and diverse ecosystems keep plants healthy naturally.'],
                ['icon'=>'bi-brightness-high-fill','title'=>'Seasonal Focus','text'=>'We grow what thrives in our climate without forcing seasons.'],
              ] as $i)
              <div class="text-center">
                <div class="mx-auto mb-3 rounded-full flex items-center justify-center"
                     style="width:64px;height:64px;background-color:rgba(151,101,90,.10);">
                  <i class="bi {{ $i['icon'] }} text-2xl" style="color:#97655A;"></i>
                </div>
                <h4 class="font-semibold mb-1">{{ $i['title'] }}</h4>
                <p class="text-gray-700">{{ $i['text'] }}</p>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </section>

      {{-- SECTION: Farm Experiences --}}
      <section class="py-12" style="background:#FAF6F2;">
        <div class="container mx-auto px-4">
          <div class="text-center mb-10">
            <h2 class="font-bold mb-2"
                style="color:#97655A; font-family:gotham, sans-serif; font-size:clamp(1.6rem,3vw,2.1rem);">
              Farm Experiences
            </h2>
            <p class="mx-auto text-gray-700" style="max-width:760px;">
              Connect with your food and learn about sustainable agriculture through hands-on activities
            </p>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Card 1 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
              <img src="{{ asset('assets/images/carousel/animal and wellness.jpg') }}"
                   alt="Animal Feeding Experience"
                   class="w-full h-44 object-cover rounded-lg mb-4">
              <h4 class="font-semibold mb-1" style="color:#97655A;">Animal Feeding Experience</h4>
              <p class="text-gray-700 mb-3">Hands-on feeding with guidance from our farmers. Perfect for families and school groups.</p>
              <div class="flex items-center text-sm mb-2" style="color:#97655A;">
                <i class="far fa-clock mr-2"></i> Weekends 9AM–2PM
              </div>
              <div class="flex items-center text-sm mb-4 text-gray-700">
                <i class="fas fa-users mr-2" style="color:#97655A;"></i> Groups up to 20 people
              </div>
              <a href="#contact"
                 class="w-full inline-flex items-center justify-center border rounded-md px-4 py-2 font-medium"
                 style="border-color:#97655A; color:#97655A;">
                Reserve Your Spot
              </a>
            </div>

            {{-- Card 2 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
              <img src="{{ asset('assets/images/carousel/organic1.jpg') }}"
                   alt="Harvest Days"
                   class="w-full h-44 object-cover rounded-lg mb-4">
              <h4 class="font-semibold mb-1" style="color:#97655A;">Harvest Days</h4>
              <p class="text-gray-700 mb-3">Join seasonal harvests and take home fresh-picked produce. Learn traditional techniques.</p>
              <div class="flex items-center text-sm mb-2" style="color:#97655A;">
                <i class="far fa-clock mr-2"></i> Morning hours, seasonal
              </div>
              <div class="flex items-center text-sm mb-4 text-gray-700">
                <i class="fas fa-seedling mr-2" style="color:#97655A;"></i> Take home your harvest
              </div>
              <a href="#contact"
                 class="w-full inline-flex items-center justify-center border rounded-md px-4 py-2 font-medium"
                 style="border-color:#97655A; color:#97655A;">
                 Learn More
              </a>
            </div>

            {{-- Card 3 --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
              <div class="relative">
                <img src="{{ asset('assets/images/carousel/games-grounds4.jpg') }}"
                     alt="Farm Tours"
                     class="w-full h-44 object-cover rounded-lg mb-4">
                <span class="absolute top-3 right-3 inline-flex items-center gap-1 text-white text-xs px-2 py-1 rounded"
                      style="background: rgba(0,0,0,.55);">
                  <i class="fas fa-route mr-1"></i> Guided Tour
                </span>
              </div>
              <h4 class="font-semibold mb-1" style="color:#97655A;">Farm Tours</h4>
              <p class="text-gray-700 mb-3">See regenerative agriculture first-hand—learn about soil health & biodiversity.</p>
              <div class="flex items-center text-sm mb-2" style="color:#97655A;">
                <i class="far fa-clock mr-2"></i> By appointment
              </div>
              <div class="flex items-center text-sm mb-4 text-gray-700">
                <i class="fas fa-graduation-cap mr-2" style="color:#97655A;"></i> Educational content included
              </div>
              <a href="#contact"
                 class="w-full inline-flex items-center justify-center border rounded-md px-4 py-2 font-medium"
                 style="border-color:#97655A; color:#97655A;">
                 Schedule Tour
              </a>
            </div>
          </div>
        </div>
      </section>

      {{-- SECTION: Visit or Contact Us --}}
      <section id="contact" class="py-12" style="background:#FFF;">
        <div class="container mx-auto px-4">
          <div class="text-center mb-10">
            <h2 class="font-bold mb-2"
                style="color:#97655A; font-family:gotham, sans-serif; font-size:clamp(1.6rem,3vw,2.1rem);">
              Visit or Contact Us
            </h2>
            <p class="mx-auto text-gray-700" style="max-width:760px;">
              We’d love to hear from you about visiting, volunteering, or partnership opportunities.
            </p>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Form --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
              <h3 class="font-semibold mb-4" style="color:#97655A;">Send an Inquiry</h3>
              <form method="POST" action="{{ route('contact.submit') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <input name="name" required placeholder="Full Name"
                         class="w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2"
                         style="--tw-ring-color:#97655A"/>
                  <input name="email" type="email" required placeholder="Email Address"
                         class="w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2"
                         style="--tw-ring-color:#97655A"/>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <input name="phone" placeholder="Phone"
                         class="w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2"
                         style="--tw-ring-color:#97655A"/>
                  <select name="subject" class="w-full border border-gray-200 rounded-md px-3 py-2 text-gray-600 focus:outline-none focus:ring-2"
                          style="--tw-ring-color:#97655A">
                    <option value="" selected disabled>Select an option</option>
                    <option>Visit</option>
                    <option>Experience Booking</option>
                    <option>Partnership</option>
                    <option>Other</option>
                  </select>
                </div>
                <textarea name="message" rows="4" placeholder="Message"
                          class="w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2"
                          style="--tw-ring-color:#97655A"></textarea>

                <button type="submit"
                        class="w-full rounded-md px-4 py-2 font-medium text-white"
                        style="background:#97655A;">
                  Send Message
                </button>
              </form>
            </div>

            {{-- Side info --}}
            <div class="space-y-6">
              <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-semibold mb-4" style="color:#97655A;">Visit Our Farm</h3>
                <ul class="space-y-3 text-gray-700">
                  <li class="flex">
                    <i class="fa fa-location-dot mt-1 mr-3" style="color:#97655A;"></i>
                    Purok 3, Brgy. Buso-buso, Calaca, Philippines
                  </li>
                  <li class="flex">
                    <i class="fa fa-phone mt-1 mr-3" style="color:#97655A;"></i>
                    +63 905 129 4741
                  </li>
                  <li class="flex">
                    <i class="fa fa-envelope mt-1 mr-3" style="color:#97655A;"></i>
                    <a href="mailto:calaia@calaia.com" class="hover:underline">calaia@calaia.com</a>
                  </li>
                  <li class="flex">
                    <i class="fa fa-clock mt-1 mr-3" style="color:#97655A;"></i>
                    Monday–Saturday: 9AM – 3PM
                  </li>
                  <li class="flex items-center gap-3">
                    <span class="font-medium">Follow us</span>
                    <a href="#" aria-label="Instagram" class="text-gray-600 hover:text-gray-800"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Facebook" class="text-gray-600 hover:text-gray-800"><i class="fab fa-facebook"></i></a>
                    <a href="#" aria-label="Messenger" class="text-gray-600 hover:text-gray-800"><i class="fab fa-facebook-messenger"></i></a>
                  </li>
                </ul>
              </div>

              <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="font-semibold mb-3" style="color:#97655A;">Location and Directions</h3>
                <div class="rounded-lg overflow-hidden mb-3">
                  <iframe
                    src="https://www.google.com/maps?q=Calaca%20Nature%20Park&output=embed"
                    class="w-full h-56 border-0"
                    loading="lazy"
                  ></iframe>
                </div>
                <h4 class="font-semibold mb-1" style="color:#97655A;">Getting Here</h4>
                <p class="text-gray-700">
                  Calaia Nature Park is located in Calaca, Batangas along the main road near Sitio Calas from the town proper of Calaca.
                </p>
                <a href="https://maps.google.com" target="_blank" class="inline-flex items-center font-medium mt-2"
                   style="color:#97655A;">
                  Get Directions with Google Maps <i class="fas fa-chevron-right text-xs ml-2"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

  {{-- Footer --}}
  <x-footer />

    {{-- Swiper JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const heroSwiper = new Swiper('.heroSwiper', {
          autoplay: { delay: 3000, disableOnInteraction: false },
          loop: true,
          effect: 'fade',
          fadeEffect: { crossFade: true },
          keyboard: { enabled: true },
          a11y: {
            prevSlideMessage: 'Previous slide',
            nextSlideMessage: 'Next slide',
          },
        });
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
</html>
