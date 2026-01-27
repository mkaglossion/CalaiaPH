<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin Panel')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <!-- Flatpickr CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- Flatpickr JS -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <!-- Icons and SweetAlert -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/lucide@latest"></script>

</head>

<body x-data="{ sidebarOpen: false }" class="bg-gray-100 flex">

  {{-- Sidebar --}}
  <aside 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-64 md:translate-x-0'"
    class="fixed top-0 left-0 w-64 bg-[#8f5d54] text-white h-screen flex flex-col justify-between transition-transform duration-300 z-50">

    {{-- Top Section --}}
    <div>
      <div class="flex items-center justify-between px-6 py-4 border-b border-[#8f5d54]/50">
        <h1 class="text-2xl font-bold tracking-wide">Admin Panel</h1>
        <button @click="sidebarOpen = false" class="md:hidden">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      {{-- Navigation --}}
      <nav class="mt-4 space-y-1 overflow-y-auto" style="max-height: calc(100vh - 100px);">
        @php
          $menuItems = [
            ['title'=>'Dashboard','icon'=>'home','route'=>'admin.dashboard'],
            ['title'=>'Home','icon'=>'home','route'=>'admin.home.*','sub'=>[
              ['label'=>'Pages Information','route'=>'admin.home.home'],
              ['label'=>'Event Experience','route'=>'admin.eventExperience.sections'],
              ['label'=>'Testimonials','route'=>'admin.testimonials.index']
            ]],
            ['title'=>'Bookings','icon'=>'calendar-check','route'=>'admin.bookings.*','sub'=>[
              ['label'=>'All Bookings','route'=>'admin.bookings.index'],
              ['label'=>'Inventory Monitor','route'=>'admin.bookings.inventory']
            ]],
            ['title'=>'Day Tour','icon'=>'sun','route'=>'admin.day.*','sub'=>[
              ['label'=>'Pages Information','route'=>'admin.day.daytour'],
              ['label'=>'Items','route'=>'admin.day.day']
            ]],
            ['title'=>'Where to Stay','icon'=>'building','route'=>'admin.stay.*','sub'=>[
              ['label'=>'Pages Information','route'=>'admin.stay.wheretostay'],
              ['label'=>'Variants','route'=>'admin.stay.staycations'],
              ['label'=>'Accommodations','route'=>'admin.stay.accommodations']
            ]],
            ['title'=>'What to Do','icon'=>'map-pin','route'=>'admin.do.*','sub'=>[
              ['label'=>'Pages Information','route'=>'admin.do.whattodo'],
              ['label'=>'Items','route'=>'admin.do.activities']
            ]],
            ['title'=>'Food & Beverage','icon'=>'map-pin','route'=>'admin.do.*','sub'=>[
              ['label'=>'Pages Information','route'=>'admin.food.foodandbeverage'],
              ['label'=>'Items','route'=>'admin.food.menus'],
              ['label'=>'Food Management','route'=>'admin.foods.index'],
              ['label'=>'Inventory Monitor','route'=>'admin.inventory.food'],
            ]],
            ['title'=>'Special Events','icon'=>'calendar','route'=>'admin.specialEvents.*','sub'=>[
              ['label'=>'Pages Information','route'=>'admin.specialEvents.specialevents']
            ]],
            ['title'=>'Our Farm','icon'=>'leaf','route'=>'admin.farm.*','sub'=>[
              ['label'=>'Pages Information','route'=>'admin.farm.ourfarm'],
              ['label'=>'Items','route'=>'admin.farm.farmInfos']
            ]],
            ['title'=>'Shop','icon'=>'shopping-bag','route'=>'admin.shop.*','sub'=>[
              ['label'=>'Pages Information','route'=>'admin.shop.shopstore'],
              ['label'=>'Items','route'=>'admin.shop.shopitems'],
              ['label'=>'Inventory Monitor','route'=>'admin.inventory.shop']
            ]],
            ['title'=>'Pricing','icon'=>'coins','route'=>'admin.pricing.*','sub'=>[
              ['label'=>'Rate Matrix','route'=>'admin.pricingConfig.index'],
            ]],
                
          ];
        @endphp

        @foreach($menuItems as $item)
          @if(!empty($item['sub']))
            <div x-data="{ open: {{ request()->routeIs($item['route']) ? 'true' : 'false' }} }" class="space-y-1">
              <button @click="open = !open" 
                class="w-full flex items-center justify-between px-6 py-2 rounded-lg transition
                {{ request()->routeIs($item['route']) ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}">
                <div class="flex items-center gap-3">
                  <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                  <span>{{ $item['title'] }}</span>
                </div>
                <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform"></i>
              </button>

              <div x-cloak x-show="open" x-transition class="pl-10 space-y-1 mt-1">
                @foreach($item['sub'] as $sub)
                  <a href="{{ route($sub['route']) }}" class="flex items-center gap-2 py-1 transition
                    {{ request()->routeIs($sub['route']) ? 'text-yellow-200' : 'hover:text-gray-300' }}">
                    <i data-lucide="home" class="w-4 h-4"></i> <span>{{ $sub['label'] }}</span>
                  </a>
                @endforeach
              </div>
            </div>
          @else
            <a href="{{ route($item['route']) }}" class="flex items-center gap-3 px-6 py-2 rounded-lg transition
              {{ request()->routeIs($item['route']) ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}">
              <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
              <span>{{ $item['title'] }}</span>
            </a>
          @endif
        @endforeach
      </nav>
    </div>

    {{-- Bottom Section --}}
    <div class="px-6 py-4 border-t border-[#8f5d54]/50">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex items-center gap-3 hover:text-gray-300 transition">
          <i data-lucide="log-out" class="w-5 h-5"></i>
          <span>Log Out</span>
        </button>
      </form>
    </div>
  </aside>

  {{-- Main Content --}}
  <main class="flex-1 ml-64 overflow-y-auto h-screen p-8">
    {{-- Flash Messages --}}
    @if (session('success'))
      <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
          <i class="fas fa-times"></i>
        </button>
      </div>
    @endif

    @if (session('error'))
      <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
          <i class="fas fa-times"></i>
        </button>
      </div>
    @endif

    @yield('content')
  </main>

  <script>
    lucide.createIcons();
  </script>

  @stack('scripts')
</body>
</html>
