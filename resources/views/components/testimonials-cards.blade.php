<section class="relative py-12 min-h-[500px]"
  style="background: linear-gradient(rgba(143, 93, 84, 0.5), rgba(143, 93, 84, 0.5)),
  url('/assets/images/carousel/our-farm.jpg'); background-size: cover; background-position: center; background-attachment: fixed;">

  @php
    $testimonials = [
      [
        "quote" => "Calaia was an unforgettable experience. The farm stay was peaceful, the activities were fun, and the food was amazing. Highly recommend!",
        "name" => "Sarah Johnson",
        "role" => "Family Traveler",
        "avatar" => "/assets/images/testimonials/avatar1.jpg",
      ],
      [
        "quote" => "The staff was incredibly welcoming, and the farm-to-table experience was outstanding. We felt completely at home.",
        "name" => "Michael Chen",
        "role" => "Adventure Seeker",
        "avatar" => "/assets/images/testimonials/avatar2.jpg",
      ],
      [
        "quote" => "Perfect getaway for our anniversary. The romantic setting and personalized service made it truly special.",
        "name" => "Emma Rodriguez",
        "role" => "Romantic Getaway",
        "avatar" => "/assets/images/testimonials/avatar3.jpg",
      ],
    ];
  @endphp

  <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-10">
      <h2 class="mt-8 font-bold tracking-tight text-white uppercase"
          style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
        WHAT OUR GUESTS SAY ABOUT CALAIA
      </h2>
      <p class="mt-3 text-white"
         style="font-style: italic; font-size: 13px; font-family: 'Gotham Book', sans-serif;">
        Hear from those who've experienced Calaia.
      </p>
      <div class="mt-6 mx-auto h-px w-80 bg-white"></div>
    </div>

    {{-- Carousel-style row (mobile scroll), cropped sides on desktop --}}
    <div class="relative">
      <div
        class="flex gap-6 md:gap-10 items-stretch md:justify-center
               overflow-x-auto md:overflow-hidden
               snap-x snap-mandatory
               px-2 sm:px-6 md:px-0
               md:-mx-24
               pb-4
               [scrollbar-width:none] [-ms-overflow-style:none]"
        style="scrollbar-width:none;"
      >
        {{-- hide scrollbar (webkit) --}}
        <style>
          .no-scrollbar::-webkit-scrollbar { display: none; }
        </style>

        @foreach($testimonials as $idx => $t)
          <article
            class="no-scrollbar snap-center shrink-0
                   w-[280px] sm:w-[320px] md:w-[360px]
                   bg-white rounded-[28px]
                   px-7 py-8 sm:px-9 sm:py-10
                   text-center
                   shadow-[0_10px_30px_rgba(0,0,0,0.12)]
                   md:scale-100
                   transition-transform duration-300"
          >
            {{-- avatar circle (like screenshot) --}}
            <div class="mx-auto mb-7 w-20 h-20 rounded-full overflow-hidden bg-[#7a7a7a] flex items-center justify-center">
              @if(!empty($t['avatar']))
                <img src="{{ $t['avatar'] }}" alt="{{ $t['name'] }}"
                     class="w-full h-full object-cover">
              @endif
            </div>

            {{-- quote (minimal + italic) --}}
            <p class="text-[#8f5d54] italic leading-[1.8]"
               style="font-size: 12px; font-family: 'Gotham Book', sans-serif;">
              “{{ $t['quote'] }}”
            </p>

            {{-- optional name/role (keep subtle) --}}
            <div class="mt-6">
              <p class="font-semibold text-[#8f5d54]"
                 style="font-size: 12px; font-family: 'Gotham Book', sans-serif;">
                {{ $t['name'] }}
              </p>
              <p class="text-[#8f5d54]/80"
                 style="font-size: 11px; font-family: 'Gotham Book', sans-serif;">
                {{ $t['role'] }}
              </p>
            </div>
          </article>
        @endforeach
      </div>
    </div>

  </div>
</section>
