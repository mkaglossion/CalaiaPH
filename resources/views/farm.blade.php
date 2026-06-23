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
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
          <div class="max-w-4xl mx-auto">
            <x-section-header
              id="sustainable-farming-heading"
              title="Our Sustainable Farming"
              subtitle="Discover how we raise livestock and grow produce with respect for nature and animal welfare"
            />
          </div>

          {{-- ============================================================
    Featured: Ethically raised livestock (overlay + checklist card)
  ============================================================ --}}
  <div class="space-y-8">

    {{-- TOP: Image banner with text overlay --}}
    <section class="relative overflow-hidden rounded-3xl shadow-lg">
      <img
        src="{{ \App\Helpers\ImageHelper::src('assets/images/carousel/animal and wellness.jpg') }}"
        alt="Free-range livestock grazing in open pastures at Calaia farm"
        class="w-full h-80 md:h-105 object-cover"
        loading="lazy"
        onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';"
      >

      {{-- dark overlay (for readability like screenshot) --}}
      <div class="absolute inset-0 bg-black/45"></div>

      {{-- content --}}
      <div class="absolute inset-0 flex items-end">
        <div class="p-8 md:p-12 max-w-3xl">
          <h3 class="text-white font-extrabold text-xl md:text-2xl leading-tight">
            Ethically raised livestock.
          </h3>
          <p class="mt-3 text-white/90 text-base md:text-lg leading-relaxed">
            Our animals live as nature intended: with plenty of space to roam, fresh air,
            and a natural diet free from antibiotics and growth hormones.
          </p>
        </div>
      </div>
    </section>

    {{-- BOTTOM: Brown checklist + circular image --}}
    @php
      $ethicalFeatures = [
        'Pasture-raised with daily access to open fields.',
        'No antibiotics or hormones - ever.',
        'Non-GMO, locally-sourced feed.',
        'Humane treatment with low-stress handling.',
      ];
    @endphp

    <section class="relative overflow-hidden rounded-3xl bg-[#6b4a2f] shadow-lg">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">
        {{-- checklist --}}
        <div class="p-10 md:p-12 pt-14">
          <ul class="space-y-6 md:space-y-10 pt-4">
            @foreach ($ethicalFeatures as $index => $text)
              <li class="flex items-start gap-6{{ $index === 0 ? ' mt-4' : '' }}">
                {{-- check icon --}}
                <span class="shrink-0 w-12 h-12 rounded-full border-4 border-white/80 flex items-center justify-center">
                  <svg viewBox="0 0 24 24" class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6L9 17l-5-5" />
                  </svg>
                </span>

                <p class="text-white font-semibold text-lg md:text-xl leading-relaxed">
                  {{ $text }}
                </p>
              </li>
            @endforeach
          </ul>
        </div>

        {{-- circular image side --}}
        <div class="relative min-h-80 lg:min-h-105">
          {{-- This makes the big circle crop on the right --}}
          <div class="absolute inset-0">
            <div class="absolute right-0 top-1/2 -translate-y-1/2 w-80 h-80 md:w-110 md:h-110 rounded-full overflow-hidden">
              <img
                src="{{ \App\Helpers\ImageHelper::src('assets/images/carousel/animals.jpg') }}"
                alt="Livestock grazing at Calaia farm"
                class="w-full h-full object-contain"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';"
              >
            </div>
          </div>

          {{-- subtle fade on right edge for smoother blend --}}
          <div class="absolute inset-y-0 right-0 w-1/3 bg-linear-to-l from-black/30 to-transparent pointer-events-none"></div>
        </div>
      </div>
    </section>

  </div>

          {{-- Meat Products Grid --}}
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16 mb-8">
            @foreach ([
              ['img'=>'our-farm.jpg','title'=>'Grass-Fed Beef','desc'=>'100% grass-fed and grass-finished diet; higher in healthy Omega-3s.'],
              ['img'=>'our-farm2.jpg','title'=>'Heritage Pork','desc'=>'Small groups with room to root and explore for superior flavor and texture.'],
              ['img'=>'organic.png','title'=>'Pasture Lamb','desc'=>'Grazed on diverse pastures for naturally complex flavor.'],
            ] as $product)
            <article class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-lg transition-shadow duration-300">
              <img src="{{ \App\Helpers\ImageHelper::src('assets/images/carousel/'.$product['img']) }}" alt="{{ $product['title'] }} product"
                   class="w-full h-48 object-cover mb-4" loading="lazy" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
              <h4 class="font-semibold text-[#8f5d54] mb-2 text-lg md:text-xl">{{ $product['title'] }}</h4>
              <p class="text-gray-600 mb-4 leading-relaxed text-sm md:text-base">{{ $product['desc'] }}</p>
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-[#8f5d54]">Available seasonally</span>
                <i class="fas fa-leaf text-green-500" aria-hidden="true"></i>
              </div>
            </article>
            @endforeach
          </div>

          {{-- ============================================================
    Fresh From Our Fields (Overlay Banner Cards)
  ============================================================ --}}
  </div>
      </section>

      {{-- Fresh From Our Fields - Full Width --}}
      <section class="space-y-0">

        {{-- Vegetable Garden --}}
        <article class="relative overflow-hidden shadow-lg group">
          <div class="absolute inset-0">
            <img src="{{ \App\Helpers\ImageHelper::src('assets/images/carousel/vegetable garden.jpg') }}" alt="Vegetable Garden" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';"/>
            <div class="absolute inset-0 bg-black/30"></div>
          </div>

          <div class="relative z-10 px-6 md:px-10 py-10 md:py-14 max-w-xl">
            <h3 class="text-white font-extrabold text-2xl md:text-4xl mb-5">Vegetable Garden.</h3>
            <p class="text-white/90 text-base md:text-lg leading-relaxed mb-8">We grow over 50 varieties of vegetables using organic methods and natural amendments like compost and cover crops.</p>
            <a href="#contact" class="inline-flex items-center bg-brand text-white font-semibold px-6 py-3 rounded-full hover:bg-brand/90 transition">GET OUR HARVEST SCHEDULE <span class="ml-2">></span></a>
          </div>
          <div class="h-80 md:h-100"></div>
        </article>

        {{-- Orchard Fruits --}}
        <article class="relative overflow-hidden shadow-lg group">
          <div class="absolute inset-0">
            <img src="{{ \App\Helpers\ImageHelper::src('assets/images/carousel/orchard fruits.jpg') }}" alt="Orchard Fruits" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';"/>
            <div class="absolute inset-0 bg-black/30"></div>
          </div>

          <div class="relative z-10 px-6 md:px-10 py-10 md:py-14 max-w-xl ml-auto text-right">
            <h3 class="text-white font-extrabold text-2xl md:text-4xl mb-5">Orchard Fruits.</h3>
            <p class="text-white/90 text-base md:text-lg leading-relaxed mb-8">Our heritage fruit trees are grown without chemical sprays, allowing natural ecosystems to maintain balance.</p>
            <a href="#contact" class="inline-flex items-center bg-brand text-white font-semibold px-6 py-3 rounded-full hover:bg-brand/90 transition">JOIN OUR FRUIT CSA <span class="ml-2">></span></a>
          </div>
          <div class="h-80 md:h-100"></div>
        </article>

      </section>

      {{-- ============================================================
        Growing With Nature (Icon Feature Block)
      ============================================================ --}}
      <section class="py-20" aria-labelledby="growing-nature-heading">
        <div class="max-w-6xl mx-auto px-6">
          {{-- Card Container --}}
          <div class="bg-[#3f6668] rounded-3xl py-10 md:py-14 px-6 md:px-14 text-white text-center shadow-lg" role="region" aria-labelledby="growing-nature-heading">
            {{-- Title --}}
            <h2 id="growing-nature-heading" class="text-2xl md:text-4xl font-extrabold mb-6">
              Growing with nature.
            </h2>

            {{-- Divider Line --}}
            <div class="w-3/4 mx-auto border-t border-white/30 mb-12" aria-hidden="true"></div>

            {{-- Icon Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12 place-items-center" role="list">
              {{-- Soil First --}}
              <div class="flex flex-col items-center text-center" role="listitem">
                {{-- Icon Circle --}}
                <div class="w-24 h-24 mx-auto rounded-full bg-white flex items-center justify-center mb-6 shadow-md">
                  <img
                    src="{{ \App\Helpers\ImageHelper::src('assets/icons/noun-soil-7519091.png') }}"
                    alt=""
                    class="w-20 h-20 object-contain"
                    loading="lazy"
                    aria-hidden="true"
                  >
                </div>

                <h3 class="text-xl font-bold mb-3 text-white">
                  Soil First
                </h3>

                <p class="text-white/80 leading-relaxed text-sm md:text-base max-w-xs">
                  We build our soil health through composting, cover crops, and minimal tillage.
                </p>
              </div>

              {{-- Biodiversity --}}
              <div class="flex flex-col items-center text-center" role="listitem">
                <div class="w-24 h-24 mx-auto rounded-full bg-white flex items-center justify-center mb-6 shadow-md">
                  <img
                    src="{{ \App\Helpers\ImageHelper::src('assets/icons/noun-biodiversity-8058278.png') }}"
                    alt=""
                    class="w-25 h-25 object-contain"
                    loading="lazy"
                    aria-hidden="true"
                  >
                </div>

                <h3 class="text-xl font-bold mb-3 text-white">
                  Biodiversity
                </h3>

                <p class="text-white/80 leading-relaxed text-sm md:text-base max-w-xs">
                  Companion planting and diverse ecosystems keep plants healthy naturally.
                </p>
              </div>

              {{-- Seasonal Focus --}}
              <div class="flex flex-col items-center text-center" role="listitem">
                <div class="w-24 h-24 mx-auto rounded-full bg-white flex items-center justify-center mb-6 shadow-md">
                  <img
                    src="{{ \App\Helpers\ImageHelper::src('assets/icons/noun-plant-7968466.png') }}"
                    alt=""
                    class="w-25 h-25 object-contain"
                    loading="lazy"
                    aria-hidden="true"
                  >
                </div>

                <h3 class="text-xl font-bold mb-3 text-white">
                  Seasonal Focus
                </h3>

                <p class="text-white/80 leading-relaxed text-sm md:text-base max-w-xs">
                  We grow what thrives in our climate without forcing seasons.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- SECTION: Farm Experiences --}}
      <section class="py-16" style="background-color: #f5efe7;" aria-labelledby="farm-experiences-heading">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
          <div class="max-w-4xl mx-auto">
            <x-section-header
              id="farm-experiences-heading"
              title="Farm Experiences"
              subtitle="Connect with your food and learn about sustainable agriculture through hands-on activities"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Experience Cards --}}
            @forelse ($farmExperiences as $experience)
            <article class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 group">
              <div class="relative">
                <img src="{{ $experience['img'] }}"
                     alt="{{ $experience['title'] }} at Calaia farm"
                     class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                @if(!empty($experience['badge']))
                <span class="absolute top-4 right-4 bg-black/70 text-white text-xs px-3 py-1 rounded-full font-medium">
                  <i class="fas fa-route mr-1" aria-hidden="true"></i> {{ $experience['badge'] }}
                </span>
                @endif
              </div>
              <div class="p-6">
                <h4 class="font-semibold text-[#8f5d54] mb-3 text-lg md:text-xl">{{ $experience['title'] }}</h4>
                <p class="text-gray-600 mb-4 leading-relaxed text-sm md:text-base">{{ $experience['desc'] }}</p>
                <div class="space-y-2 mb-6">
                  <div class="flex items-center text-sm text-[#8f5d54]">
                    <i class="far fa-clock mr-2" aria-hidden="true"></i> {{ $experience['time'] }}
                  </div>
                  <div class="flex items-center text-sm text-gray-600">
                    <i class="bi bi-{{ $experience['icon'] }} mr-2 text-[#8f5d54]" aria-hidden="true"></i> {{ $experience['capacity'] }}
                  </div>
                </div>
                <a href="/contact"
                   class="w-full inline-flex items-center justify-center border-2 border-brand text-brand hover:bg-brand hover:text-white rounded-xl px-6 py-3 font-medium transition-all duration-300">
                  {{ $experience['cta'] }}
                </a>
              </div>
            </article>
            @empty
            <div class="col-span-3 text-center py-12">
              <p class="text-gray-500">No experiences available at the moment.</p>
            </div>
            @endforelse
          </div>
        </div>
      </section>
    </main>

    {{-- Floating Buttons --}}
    <x-floating-buttons />
</x-page-layout>
