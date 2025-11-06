<section class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden text-slate-800">
  <!-- Header Section with Gradient -->
  <div class="bg-white text-black p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-2">
      <h1 class="text-2xl font-bold">Weather Forecast</h1>
      <div class="inline-flex items-center gap-2 text-gray-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 11.5a3 3 0 100-6 3 3 0 000 6z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M19.5 10.5c0 5.25-7.5 11.25-7.5 11.25S4.5 15.75 4.5 10.5a7.5 7.5 0 1115 0z"/>
        </svg>
        <span class="font-medium">{{ $data['location'] }}</span>
      </div>
    </div>

    <!-- Current Weather -->
    <div class="flex items-center justify-between mt-4">
      <div>
        <div class="text-5xl font-bold">{{ $data['now']['temp'] }}°C</div>
        <div class="text-lg mt-1 opacity-90">{{ $data['now']['condition'] }}</div>
      </div>
      <div class="text-5xl opacity-90">
        <i class="fas fa-cloud-sun"></i>
      </div>
    </div>

    <!-- Weather Details -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 text-center">
      <div>
        <div class="text-sm opacity-80">Feels like</div>
        <div class="font-semibold text-lg">{{ $data['now']['feels_like'] }}°C</div>
      </div>
      <div>
        <div class="text-sm opacity-80">Humidity</div>
        <div class="font-semibold text-lg">{{ $data['now']['humidity'] }}%</div>
      </div>
      <div>
        <div class="text-sm opacity-80">Rain</div>
        <div class="font-semibold text-lg">{{ $data['now']['rain'] }}%</div>
      </div>
    </div>
  </div>
  
  <!-- Hourly Forecast -->
  <div class="p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
      <i class="fas fa-clock text-amber-500"></i>
      Hourly Forecast
    </h2>

    <div class="overflow-x-auto">
      <div class="flex space-x-4 min-w-max">
        @foreach($data['hourly'] as $hour)
        <div class="text-center min-w-[80px]">
          <div class="font-medium text-gray-700 text-sm">{{ $hour['time'] }}</div>
          <div class="text-2xl font-bold my-2">{{ $hour['temp'] }}°C</div>
          <div class="text-xs text-gray-500 bg-gray-100 rounded-full py-1">{{ $hour['rain'] }}%</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  
  <!-- Weather Tips -->
  <div class="bg-blue-50 p-6 border-t border-blue-100">
    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
      <i class="fas fa-lightbulb text-cyan-600"></i>
      Weather Tips For Your Visit
    </h2>

    <ul class="space-y-3">
      @foreach($data['tips'] as $tip)
      <li class="flex items-start">
        <i class="fas fa-lightbulb text-blue-500 mt-1 mr-3"></i>
        <span class="text-slate-700">{{ $tip }}</span>
      </li>
      @endforeach
    </ul>
  </div>
</section>