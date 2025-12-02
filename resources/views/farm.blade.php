<x-page-layout
    title="CalaiaPH - Farm"
    description="Visit our beautiful farm at CalaiaPH. Learn about sustainable farming, enjoy farm tours, and experience the source of our fresh, organic produce firsthand."
    keywords="organic farm, sustainFable farming, farm tours, fresh produce, CalaiaPH farm, Philippines agriculture, farm visit"
    active-header="farm"
>
    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @endpush

    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

    <main id="main" class="">
      {{-- SECTION: Our Sustainable Farming --}}
      <section class="py-16" style="background-color: #f5efe7;" aria-labelledby="sustainable-farming-heading">
        <div class="container mx-auto px-4 max-w-7xl">
          <x-section-header
              id="sustainable-farming-heading"
              title="Our Sustainable Farming"
              subtitle="Discover how we raise livestock and grow produce with respect for nature and animal welfare"
          />

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-16">
            <figure class="order-2 lg:order-1">
              <img
                src="{{ asset('assets/images/carousel/animal and wellness.jpg') }}"
                alt="Free-range livestock grazing in open pastures at Calaia farm"
                class="w-full h-80 md:h-96 object-cover rounded-2xl shadow-lg"
                loading="lazy"
              >
              <figcaption class="sr-only">Free-range livestock in natural environment</figcaption>
            </figure>

            <div class="order-1 lg:order-2">
              <h3 class="font-bold text-[#8f5d54] mb-6" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                Ethically Raised Livestock
              </h3>
              <p class="text-gray-700 mb-8 leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                Our animals live as nature intended: with plenty of space to roam, fresh air, and a natural diet free from antibiotics and growth hormones.
              </p>
              <x-feature-list
                  :features="[
                      'Pasture-raised with daily access to open fields',
                      'No antibiotics or hormones — ever',
                      'Non-GMO, locally-sourced feed',
                      'Humane treatment with low-stress handling',
                  ]"
              />
            </div>
          </div>

          {{-- Meat Products Grid --}}
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach ([
              ['img'=>'our-farm.jpg','title'=>'Grass-Fed Beef','desc'=>'100% grass-fed and grass-finished diet; higher in healthy Omega-3s.'],
              ['img'=>'our-farm2.jpg','title'=>'Heritage Pork','desc'=>'Small groups with room to root and explore for superior flavor and texture.'],
              ['img'=>'organic.png','title'=>'Pasture Lamb','desc'=>'Grazed on diverse pastures for naturally complex flavor.'],
            ] as $product)
            <article class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-lg transition-shadow duration-300">
              <img src="{{ asset('assets/images/carousel/'.$product['img']) }}" alt="{{ $product['title'] }} product"
                   class="w-full h-48 object-cover rounded-xl mb-4" loading="lazy">
              <h4 class="font-semibold text-[#8f5d54] mb-2" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">{{ $product['title'] }}</h4>
              <p class="text-gray-600 mb-4 leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">{{ $product['desc'] }}</p>
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-[#8f5d54]">Available seasonally</span>
                <i class="fas fa-leaf text-green-500" aria-hidden="true"></i>
              </div>
            </article>
            @endforeach
          </div>
        </div>
      </section>

      {{-- SECTION: Fresh From Our Fields --}}
      <section class="py-16 bg-linear-to-br from-green-50 to-emerald-50" aria-labelledby="fresh-fields-heading">
        <div class="container mx-auto px-4 max-w-7xl">
          <header class="text-center mb-12">
            <h2 id="fresh-fields-heading" class="font-bold text-[#8f5d54] mb-4" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
              Fresh From Our Fields
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
              Seasonal, pesticide-free produce grown with care for flavor and nutrition.
            </p>
          </header>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            {{-- Vegetable Garden --}}
            <article class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-lg transition-shadow duration-300">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <img src="{{ asset('assets/images/carousel/organic1.jpg') }}" alt="Lush vegetable garden with various organic produce"
                     class="w-full h-64 object-cover rounded-xl md:col-span-1" loading="lazy">
                <div class="md:col-span-2">
                  <h3 class="font-semibold text-[#8f5d54] mb-3" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Vegetable Garden</h3>
                  <p class="text-gray-600 mb-4 leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                    We grow over 50 varieties of vegetables using organic methods and natural amendments like compost and cover crops.
                  </p>
                  <h4 class="font-semibold text-[#8f5d54] mb-2">In Season Now:</h4>
                  <p class="text-gray-700 mb-4" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">Heirloom tomatoes, rainbow chard, zucchini, garlic, kale, peppers, eggplant</p>
                  <a href="#contact" class="inline-flex items-center text-[#8f5d54] hover:text-[#8f5d54]-dark font-medium transition-colors">
                    Get our harvest schedule <i class="fas fa-chevron-right text-xs ml-2" aria-hidden="true"></i>
                  </a>
                </div>
              </div>
            </article>

            {{-- Orchard Fruits --}}
            <article class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-lg transition-shadow duration-300">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <img src="{{ asset('assets/images/carousel/Calaia-26.jpg') }}" alt="Fruit orchard with trees in bloom"
                     class="w-full h-64 object-cover rounded-xl md:col-span-1" loading="lazy">
                <div class="md:col-span-2">
                  <h3 class="font-semibold text-[#8f5d54] mb-3" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Orchard Fruits</h3>
                  <p class="text-gray-600 mb-4 leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                    Our heritage fruit trees are grown without chemical sprays, allowing natural ecosystems to maintain balance.
                  </p>
                  <h4 class="font-semibold text-[#8f5d54] mb-2">Coming Soon:</h4>
                  <p class="text-gray-700 mb-4" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">Apples, pears, peaches, plums, cherries, persimmons</p>
                  <a href="#contact" class="inline-flex items-center text-[#8f5d54] hover:text-[#8f5d54]-dark font-medium transition-colors">
                    Join our fruit CSA <i class="fas fa-chevron-right text-xs ml-2" aria-hidden="true"></i>
                  </a>
                </div>
              </div>
            </article>
          </div>

          {{-- Growing with Nature --}}
          <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 md:p-12">
            <h3 class="font-bold text-center text-[#8f5d54] mb-8" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Growing with Nature</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
              @foreach ([
                ['icon'=>'bi-flower1','title'=>'Soil First','text'=>'We build our soil health through composting, cover crops, and minimal tillage.'],
                ['icon'=>'bi-tree-fill','title'=>'Biodiversity','text'=>'Companion planting and diverse ecosystems keep plants healthy naturally.'],
                ['icon'=>'bi-brightness-high-fill','title'=>'Seasonal Focus','text'=>'We grow what thrives in our climate without forcing seasons.'],
              ] as $feature)
              <div class="text-center">
                <div class="w-16 h-16 bg-[#8f5d54]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                  <i class="bi {{ $feature['icon'] }} text-2xl text-[#8f5d54]" aria-hidden="true"></i>
                </div>
                <h4 class="text-lg font-semibold text-[#8f5d54] mb-2">{{ $feature['title'] }}</h4>
                <p class="text-gray-600 leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">{{ $feature['text'] }}</p>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </section>

      {{-- SECTION: Farm Experiences --}}
      <section class="py-16 bg-white" aria-labelledby="farm-experiences-heading">
        <div class="container mx-auto px-4 max-w-7xl">
          <header class="text-center mb-12">
            <h2 id="farm-experiences-heading" class="font-bold text-[#8f5d54] mb-4" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
              Farm Experiences
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
              Connect with your food and learn about sustainable agriculture through hands-on activities
            </p>
          </header>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Experience Cards --}}
            @foreach ([
              [
                'img' => 'animal and wellness.jpg',
                'title' => 'Animal Feeding Experience',
                'desc' => 'Hands-on feeding with guidance from our farmers. Perfect for families and school groups.',
                'time' => 'Weekends 9AM–2PM',
                'capacity' => 'Groups up to 20 people',
                'icon' => 'people',
                'cta' => 'Reserve Your Spot'
              ],
              [
                'img' => 'organic1.jpg',
                'title' => 'Harvest Days',
                'desc' => 'Join seasonal harvests and take home fresh-picked produce. Learn traditional techniques.',
                'time' => 'Morning hours, seasonal',
                'capacity' => 'Take home your harvest',
                'icon' => 'flower1',
                'cta' => 'Learn More'
              ],
              [
                'img' => 'games-grounds4.jpg',
                'title' => 'Farm Tours',
                'desc' => 'See regenerative agriculture first-hand—learn about soil health & biodiversity.',
                'time' => 'By appointment',
                'capacity' => 'Educational content included',
                'icon' => 'mortarboard',
                'cta' => 'Schedule Tour',
                'badge' => 'Guided Tour'
              ]
            ] as $experience)
            <article class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 group">
              <div class="relative">
                <img src="{{ asset('assets/images/carousel/'.$experience['img']) }}"
                     alt="{{ $experience['title'] }} at Calaia farm"
                     class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                @if(isset($experience['badge']))
                <span class="absolute top-4 right-4 bg-black/70 text-white text-xs px-3 py-1 rounded-full font-medium">
                  <i class="fas fa-route mr-1" aria-hidden="true"></i> {{ $experience['badge'] }}
                </span>
                @endif
              </div>
              <div class="p-6">
                <h4 class="font-semibold text-[#8f5d54] mb-3" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">{{ $experience['title'] }}</h4>
                <p class="text-gray-600 mb-4 leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">{{ $experience['desc'] }}</p>
                <div class="space-y-2 mb-6">
                  <div class="flex items-center text-sm text-[#8f5d54]">
                    <i class="far fa-clock mr-2" aria-hidden="true"></i> {{ $experience['time'] }}
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="bi bi-{{ $experience['icon'] }} mr-2 text-[#8f5d54]" aria-hidden="true"></i> {{ $experience['capacity'] }}
                  </div>
                </div>
                <a href="#contact"
                   class="w-full inline-flex items-center justify-center border-2 border-[#8f5d54] text-[#8f5d54] hover:bg-[#8f5d54] hover:text-white rounded-xl px-6 py-3 font-medium transition-all duration-300">
                  {{ $experience['cta'] }}
                </a>
              </div>
            </article>
            @endforeach
          </div>
        </div>
      </section>

      {{-- SECTION: Visit or Contact Us --}}
      <section id="contact" class="py-16 bg-linear-to-br from-primary/5 to-primary/10" aria-labelledby="contact-heading">
        <div class="container mx-auto px-4 max-w-7xl">
          <header class="text-center mb-12">
            <h2 id="contact-heading" class="font-bold text-[#8f5d54] mb-4" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
              Visit or Contact Us
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
              We'd love to hear from you about visiting, volunteering, or partnership opportunities.
            </p>
          </header>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            {{-- Contact Form --}}
            <x-contact-form action="{{ route('contact.submit') }}" />

            {{-- Contact Information --}}
            <div class="space-y-8">
              <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                <h3 class="font-semibold text-[#8f5d54] mb-6" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Visit Our Farm</h3>
                <ul class="space-y-4" role="list">
                  <li class="flex items-start">
                    <i class="fa fa-location-dot text-[#8f5d54] mt-1 mr-4 shrink-0" aria-hidden="true"></i>
                    <div>
                      <span class="font-medium text-gray-900">Address</span>
                      <address class="text-gray-600 not-italic">Purok 3, Brgy. Buso-buso, Calaca, Philippines</address>
                    </div>
                  </li>
                  <li class="flex items-center">
                    <i class="fa fa-phone text-[#8f5d54] mr-4 shrink-0" aria-hidden="true"></i>
                    <div>
                      <span class="font-medium text-gray-900">Phone</span>
                      <a href="tel:+639051294741" class="text-gray-600 hover:text-[#8f5d54] transition-colors">+63 905 129 4741</a>
                    </div>
                  </li>
                  <li class="flex items-center">
                    <i class="fa fa-envelope text-[#8f5d54] mr-4 shrink-0" aria-hidden="true"></i>
                    <div>
                      <span class="font-medium text-gray-900">Email</span>
                      <a href="mailto:calaia@calaia.com" class="text-gray-600 hover:text-[#8f5d54] transition-colors">calaia@calaia.com</a>
                    </div>
                  </li>
                  <li class="flex items-start">
                    <i class="fa fa-clock text-[#8f5d54] mt-1 mr-4 shrink-0" aria-hidden="true"></i>
                    <div>
                      <span class="font-medium text-gray-900">Hours</span>
                      <span class="text-gray-600 block">Monday–Saturday: 9AM – 3PM</span>
                    </div>
                  </li>
                  <li class="flex items-center gap-4 pt-2">
                    <span class="font-medium text-gray-900">Follow us</span>
                    <div class="flex gap-3">
                      <a href="#" aria-label="Follow us on Instagram" class="text-gray-500 hover:text-[#8f5d54] transition-colors">
                        <i class="fab fa-instagram text-xl" aria-hidden="true"></i>
                      </a>
                      <a href="#" aria-label="Follow us on Facebook" class="text-gray-500 hover:text-[#8f5d54] transition-colors">
                        <i class="fab fa-facebook text-xl" aria-hidden="true"></i>
                      </a>
                      <a href="#" aria-label="Contact us on Messenger" class="text-gray-500 hover:text-[#8f5d54] transition-colors">
                        <i class="fab fa-facebook-messenger text-xl" aria-hidden="true"></i>
                      </a>
                    </div>
                  </li>
                </ul>
              </div>

              <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                <h3 class="font-semibold text-[#8f5d54] mb-6" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Location & Directions</h3>
                <div class="rounded-xl overflow-hidden mb-6 shadow-md">
                  <iframe
                    src="https://www.google.com/maps?q=Calaca%20Nature%20Park&output=embed"
                    class="w-full h-64 border-0"
                    loading="lazy"
                    title="Calaia Farm Location Map"
                  ></iframe>
                </div>
                <h4 class="text-lg font-semibold text-[#8f5d54] mb-3">Getting Here</h4>
                <p class="text-gray-600 mb-4 leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                  Calaia Nature Park is located in Calaca, Batangas along the main road near Sitio Calas from the town proper of Calaca.
                </p>
                <a href="https://maps.google.com" target="_blank" rel="noopener"
                   class="inline-flex items-center text-[#8f5d54] hover:text-[#8f5d54]-dark font-medium transition-colors">
                  Get Directions with Google Maps <i class="fas fa-chevron-right text-xs ml-2" aria-hidden="true"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    {{-- Floating Buttons --}}
    <x-floating-buttons />
</x-page-layout>

