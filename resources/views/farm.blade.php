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
    <x-hero-slider :images="$swiperImages" :texts="$heroTexts ?? []" :use-placeholder-on-error="false" />

    @php
      $ethicalSection = $farmSections->get('ethical_livestock');
      $productSection = $farmSections->get('product_cards');
      $fieldSection = $farmSections->get('field_banners');
      $natureSection = $farmSections->get('growing_nature');
      $experienceSection = $farmSections->get('farm_experiences');
      $safeColor = static function ($section, string $fallback): string {
          $color = data_get($section?->settings, 'background_color');
          return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : $fallback;
      };
    @endphp

    <main id="main">
      @if ($ethicalSection || $productSection)
      <section class="py-16" style="background-color: #f5efe7;" aria-labelledby="sustainable-farming-heading">
        <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="mx-auto max-w-4xl">
            <x-section-header
              id="sustainable-farming-heading"
              :title="$farmPageTitle ?? 'Our Sustainable Farming'"
              :subtitle="$farmPageIntro ?? 'Discover how we raise livestock and grow produce with respect for nature and animal welfare'"
            />
          </div>

          @if ($ethicalSection)
            <div class="space-y-8">
              <section class="relative min-h-80 overflow-hidden rounded-3xl shadow-lg md:min-h-105" style="background-color: {{ $safeColor($ethicalSection, '#6b4a2f') }};">
                @if (filled($ethicalSection->image_url))
                  <img
                    src="{{ \App\Helpers\ImageHelper::src($ethicalSection->image_url) }}"
                    alt="{{ $ethicalSection->title }}"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="lazy"
                    onerror="this.remove();"
                  >
                @endif
                <div class="absolute inset-0 bg-black/45"></div>
                <div class="absolute inset-0 flex items-end">
                  <div class="max-w-3xl p-8 md:p-12">
                    <h3 class="text-xl font-extrabold leading-tight text-white md:text-2xl">{{ $ethicalSection->title }}</h3>
                    @if ($ethicalSection->description)
                      <p class="mt-3 text-base leading-relaxed text-white/90 md:text-lg">{{ $ethicalSection->description }}</p>
                    @endif
                  </div>
                </div>
              </section>

              <section class="relative overflow-hidden rounded-3xl shadow-lg" style="background-color: {{ $safeColor($ethicalSection, '#6b4a2f') }};">
                <div class="grid grid-cols-1 items-stretch gap-8 {{ filled($ethicalSection->secondary_image_url) ? 'lg:grid-cols-2' : '' }}">
                  <div class="p-10 pt-14 md:p-12 {{ filled($ethicalSection->secondary_image_url) ? '' : 'lg:col-span-2' }}">
                    <ul class="space-y-6 pt-4 md:space-y-10">
                      @foreach ($ethicalSection->items ?? [] as $index => $feature)
                        @if (filled($feature['text'] ?? null))
                          <li class="flex items-start gap-6{{ $index === 0 ? ' mt-4' : '' }}">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-4 border-white/80">
                              <svg viewBox="0 0 24 24" class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 6L9 17l-5-5" />
                              </svg>
                            </span>
                            <p class="text-lg font-semibold leading-relaxed text-white md:text-xl">{{ $feature['text'] }}</p>
                          </li>
                        @endif
                      @endforeach
                    </ul>
                  </div>
                  @if (filled($ethicalSection->secondary_image_url))
                    <div class="relative min-h-80 lg:min-h-105">
                      <div class="absolute inset-0">
                        <div class="absolute right-0 top-1/2 h-80 w-80 -translate-y-1/2 overflow-hidden rounded-full md:h-110 md:w-110">
                          <img
                            src="{{ \App\Helpers\ImageHelper::src($ethicalSection->secondary_image_url) }}"
                            alt="{{ $ethicalSection->title }}"
                            class="h-full w-full object-contain"
                            loading="lazy"
                            onerror="this.remove();"
                          >
                        </div>
                      </div>
                      <div class="pointer-events-none absolute inset-y-0 right-0 w-1/3 bg-linear-to-l from-black/30 to-transparent"></div>
                    </div>
                  @endif
                </div>
              </section>
            </div>
          @endif

          @if ($productSection && count($productSection->items ?? []))
            <div class="mt-16 mb-8 grid grid-cols-1 gap-8 md:grid-cols-3">
              @foreach ($productSection->items as $product)
                <article class="rounded-2xl border border-gray-100 bg-white p-6 shadow-md transition-shadow duration-300 hover:shadow-lg">
                  @if (filled($product['image_url'] ?? null))
                    <img
                      src="{{ \App\Helpers\ImageHelper::src($product['image_url']) }}"
                      alt="{{ $product['title'] ?? 'Farm product' }}"
                      class="mb-4 h-48 w-full object-cover"
                      loading="lazy"
                      onerror="this.remove();"
                    >
                  @endif
                  <h4 class="mb-2 text-lg font-semibold text-[#8f5d54] md:text-xl">{{ $product['title'] ?? '' }}</h4>
                  <p class="mb-4 text-sm leading-relaxed text-gray-600 md:text-base">{{ $product['description'] ?? '' }}</p>
                  @if (filled($product['meta'] ?? null))
                    <div class="flex items-center justify-between">
                      <span class="text-sm font-medium text-[#8f5d54]">{{ $product['meta'] }}</span>
                      <i class="fas fa-leaf text-green-500" aria-hidden="true"></i>
                    </div>
                  @endif
                </article>
              @endforeach
            </div>
          @endif
        </div>
      </section>
      @endif

      @if ($fieldSection && count($fieldSection->items ?? []))
        <section>
          @foreach ($fieldSection->items as $banner)
            @php($alignRight = ($banner['alignment'] ?? 'left') === 'right')
            <article class="group relative overflow-hidden bg-[#6b4a2f] shadow-lg">
              <div class="absolute inset-0">
                @if (filled($banner['image_url'] ?? null))
                  <img
                    src="{{ \App\Helpers\ImageHelper::src($banner['image_url']) }}"
                    alt="{{ $banner['title'] ?? 'Farm field' }}"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                    onerror="this.remove();"
                  >
                @endif
                <div class="absolute inset-0 bg-black/40"></div>
              </div>
              <div class="relative z-10 max-w-xl px-6 py-10 md:px-10 md:py-14 {{ $alignRight ? 'ml-auto text-right' : '' }}">
                <h3 class="mb-5 text-2xl font-extrabold text-white md:text-4xl">{{ $banner['title'] ?? '' }}</h3>
                <p class="mb-0 text-base leading-relaxed text-white/90 md:text-lg">{{ $banner['description'] ?? '' }}</p>
              </div>
              <div class="h-80 md:h-100"></div>
            </article>
          @endforeach
        </section>
      @endif

      @if ($experienceSection)
        <section class="py-16" style="background-color: #f5efe7;" aria-labelledby="farm-experiences-heading">
          <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl">
              <x-section-header
                id="farm-experiences-heading"
                :title="$experienceSection->title"
                :subtitle="$experienceSection->description"
              />
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
              @forelse ($farmExperiences as $experience)
                <article class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-md transition-all duration-300 hover:shadow-lg">
                  @if (filled($experience['img'] ?? null))
                    <div class="relative">
                      <img
                        src="{{ \App\Helpers\ImageHelper::src($experience['img']) }}"
                        alt="{{ $experience['title'] }} at Calaia farm"
                        class="h-48 w-full object-cover transition-transform duration-300 group-hover:scale-105"
                        loading="lazy"
                        onerror="this.remove();"
                      >
                      @if (!empty($experience['badge']))
                        <span class="absolute top-4 right-4 rounded-full bg-black/70 px-3 py-1 text-xs font-medium text-white">
                          <i class="fas fa-route mr-1" aria-hidden="true"></i> {{ $experience['badge'] }}
                        </span>
                      @endif
                    </div>
                  @endif
                  <div class="p-6">
                    @if (!empty($experience['badge']))
                      <span class="mb-4 inline-flex rounded-full bg-black/70 px-3 py-1 text-xs font-medium text-white {{ filled($experience['img'] ?? null) ? 'hidden' : '' }}">
                        <i class="fas fa-route mr-1" aria-hidden="true"></i> {{ $experience['badge'] }}
                      </span>
                    @endif
                    <h4 class="mb-3 text-lg font-semibold text-[#8f5d54] md:text-xl">{{ $experience['title'] }}</h4>
                    <p class="mb-4 text-sm leading-relaxed text-gray-600 md:text-base">{{ $experience['desc'] }}</p>
                    <div class="mb-6 space-y-2">
                      <div class="flex items-center text-sm text-[#8f5d54]"><i class="far fa-clock mr-2" aria-hidden="true"></i> {{ $experience['time'] }}</div>
                      <div class="flex items-center text-sm text-gray-600"><i class="bi bi-{{ $experience['icon'] }} mr-2 text-[#8f5d54]" aria-hidden="true"></i> {{ $experience['capacity'] }}</div>
                    </div>
                  </div>
                </article>
              @empty
                <div class="col-span-3 py-12 text-center"><p class="text-gray-500">No experiences available at the moment.</p></div>
              @endforelse
            </div>
          </div>
        </section>
      @endif

      @if ($natureSection)
        <section class="py-20" aria-labelledby="growing-nature-heading">
          <div class="mx-auto max-w-6xl px-6">
            <div class="rounded-3xl px-6 py-10 text-center text-white shadow-lg md:px-14 md:py-14" style="background-color: {{ $safeColor($natureSection, '#3f6668') }};">
              <h2 id="growing-nature-heading" class="mb-6 text-2xl font-extrabold md:text-4xl">{{ $natureSection->title }}</h2>
              <div class="mx-auto mb-12 w-3/4 border-t border-white/30" aria-hidden="true"></div>
              <div class="grid grid-cols-1 items-start gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-10" role="list">
                @foreach ($natureSection->items ?? [] as $principle)
                  <div class="flex h-full w-full flex-col items-center text-center" role="listitem">
                    <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center">
                      @if (filled($principle['image_url'] ?? null))
                      <div class="flex h-24 w-24 items-center justify-center rounded-full bg-white shadow-md">
                        <img
                          src="{{ \App\Helpers\ImageHelper::src($principle['image_url']) }}"
                          alt=""
                          class="h-20 w-20 object-contain"
                          loading="lazy"
                          aria-hidden="true"
                          onerror="this.parentElement.remove();"
                        >
                      </div>
                      @endif
                    </div>
                    <h3 class="mb-3 flex min-h-14 w-full items-start justify-center text-xl font-bold leading-tight text-white">{{ $principle['title'] ?? '' }}</h3>
                    <p class="max-w-xs text-sm leading-relaxed text-white/80 md:text-base">{{ $principle['description'] ?? '' }}</p>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </section>
      @endif

      <x-messenger-cta
        :copy="$farmCta"
        section-class="w-full px-4 py-16 sm:px-6 md:py-20 lg:px-8"
        section-style="background-color: #f5efe7;"
        title-id="farm-cta-heading"
        title="{{ $farmCtaTitle ?? 'Ready for your farm experience?' }}"
        intro="{{ $farmCtaIntro ?? 'Message us on Messenger to ask about farm visits, group reservations, and special arrangements.' }}"
        button-url="https://m.me/410774518796277"
        button-label="{{ $farmCtaButton ?? 'Message Us on Messenger' }}"
        note="{{ $farmCtaNote ?? 'We usually reply as soon as possible during business hours.' }}"
        icon-class="fas fa-tractor text-4xl md:text-5xl opacity-80 mb-4 block"
    />
    </main>

    {{-- Floating Buttons --}}
    <x-floating-buttons />
</x-page-layout>
