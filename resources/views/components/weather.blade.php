<section class="max-w-4xl mx-auto my-12 px-4">
  {{-- CARD --}}
  <div class="bg-white overflow-hidden shadow-[0_10px_30px_rgba(0,0,0,0.12)] text-slate-800 rounded-t-[22px] rounded-b-[22px]">

    {{-- HEADER / CURRENT --}}
    <div class="p-6 rounded-t-[22px]">
      <div class="flex items-center justify-between gap-4">
        <h1 class="font-bold text-slate-900" style="font-size:25px;">
          Weather Forecast
        </h1>

        <div class="inline-flex items-center gap-2 text-slate-700">
          {{-- location icon --}}
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M12 11.5a3 3 0 100-6 3 3 0 000 6z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M19.5 10.5c0 5.25-7.5 11.25-7.5 11.25S4.5 15.75 4.5 10.5a7.5 7.5 0 1115 0z"/>
          </svg>
          <span class="uppercase tracking-wide font-semibold" style="font-size:13px;">
            {{ $data['location'] }}
          </span>
        </div>
      </div>

      <div class="mt-4">
        <div class="font-bold text-slate-900 leading-none" style="font-size:22px;">
          {{ $data['now']['temp'] }}°C
        </div>
        <div class="text-slate-600 mt-1" style="font-size:11.5px;">
          {{ $data['now']['condition'] }}
        </div>
      </div>

      {{-- HOURLY TITLE --}}
      <h1 class="mt-4 font-bold text-slate-900" style="font-size:25px;">
        Hourly Forecast
      </h1>

      {{-- HOURLY ROW --}}
      <div class="mt-3 overflow-x-auto">
        <div class="flex gap-8 min-w-max pb-2 justify-center">
          @foreach($data['hourly'] as $hour)
            <div class="text-center">
              <div class="text-slate-500" style="font-size:17px;">
                {{ $hour['time'] }}
              </div>

              <div class="font-bold text-slate-900 mt-1" style="font-size:22px;">
                {{ $hour['temp'] }}°C
              </div>

              <div class="mt-1 inline-flex items-center justify-center rounded-full px-3 py-0.5
                          bg-[#566057] text-white/95" style="font-size:14px;">
                {{ $hour['rain'] }}%
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- TIPS PANEL (dark teal like screenshot) --}}
    <div class="bg-[#3F6771] px-6 sm:px-8 py-6 rounded-b-[22px]">
      <ul class="space-y-3">
        @foreach($data['tips'] as $tip)
          <li class="flex items-start gap-3 text-white/90">
            {{-- leaf icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
              <path d="M20.77 3.23c-4.1-4.1-12.1.19-14.5 2.58C3.9 8.18 3 10.6 3 13c0 4.42 3.58 8 8 8 2.4 0 4.82-.9 7.19-3.27 2.39-2.4 6.68-10.4 2.58-14.5ZM7.7 15.3c-.39.39-1.02.39-1.41 0-.39-.39-.39-1.02 0-1.41l5.5-5.5c.39-.39 1.02-.39 1.41 0 .39.39.39 1.02 0 1.41l-5.5 5.5Z"/>
            </svg>

            <span class="" style="font-size:11.5px; line-height:1.7;">
              {{ $tip }}
            </span>
          </li>
        @endforeach
      </ul>
    </div>

  </div>
</section>
