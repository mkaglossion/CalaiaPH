<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin Panel')</title>

  @include('layouts.partials.favicon')

  {{-- Vendor CSS first --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">

  {{-- Vite --}}
  @vite(['resources/js/app.js'])

  <style>[x-cloak]{display:none !important;}</style>

  {{-- Sidebar width scales --}}
  <style>
    :root { --sidebar-w: 18rem; }
    @media (min-width: 1024px){ :root{ --sidebar-w: 17rem; } }
    @media (min-width: 1280px){ :root{ --sidebar-w: 18rem; } }
    @media (min-width: 1536px){ :root{ --sidebar-w: 20rem; } }
  </style>

  {{-- Desktop main offset (PURE CSS) --}}
  <style>
    @media (min-width: 768px) {
      .admin-main { padding-left: var(--sidebar-w); }
    }
  </style>
</head>

<body
  x-data="{
    sidebarOpen:false,
    closeSidebar(){ this.sidebarOpen=false },
    openSidebar(){ this.sidebarOpen=true }
  }"
  class="bg-gray-100 min-h-screen overflow-x-hidden"
  style="font-family: 'Montserrat', sans-serif;"
>

  {{-- Mobile Overlay --}}
  <div
    x-cloak
    x-show="sidebarOpen"
    @click="closeSidebar()"
    class="fixed inset-0 bg-black/50 z-40 md:hidden"
    x-transition.opacity
    aria-hidden="true"
  ></div>

  {{-- Sidebar --}}
  <aside
    :style="`width: var(--sidebar-w);`"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 bg-[#8f5d54] text-white flex flex-col transition-transform duration-300"
    role="navigation"
    aria-label="Admin sidebar"
  >
    {{-- Brand / Top --}}
    <div class="px-6 py-4 border-b border-white/10 flex items-center">
      <h1 class="flex-1 text-center text-4xl font-bold tracking-wide whitespace-nowrap">
        Admin Panel
      </h1>

      {{-- Close button (mobile) --}}
      <button
        type="button"
        @click="closeSidebar()"
        class="md:hidden inline-flex items-center justify-center rounded-lg p-2 hover:bg-white/10 transition"
        aria-label="Close sidebar"
      >
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    {{-- Nav (ONLY this scrolls) --}}
    <nav class="flex-1 overflow-y-auto py-3">
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
          ]],
          ['title'=>'What to Do','icon'=>'map-pin','route'=>'admin.do.*','sub'=>[
            ['label'=>'Pages Information','route'=>'admin.do.whattodo'],
            ['label'=>'Items','route'=>'admin.do.activities']
          ]],
          ['title'=>'Food & Beverage','icon'=>'map-pin','route'=>'admin.do.*','sub'=>[
            ['label'=>'Pages Information','route'=>'admin.food.foodandbeverage'],
            ['label'=>'Category Images','route'=>'admin.dining.category-images.index'],
            ['label'=>'Food Management','route'=>'admin.foods.index'],
            ['label'=>'Inventory Monitor','route'=>'admin.inventory.food'],
          ]],
          ['title'=>'Special Events','icon'=>'calendar','route'=>'admin.specialEvents.*','sub'=>[
            ['label'=>'Pages Information','route'=>'admin.specialEvents.specialevents'],
            ['label'=>'Events Manager','route'=>'admin.specialEvents.manager']
          ]],
          ['title'=>'Our Farm','icon'=>'leaf','route'=>'admin.farm.*','sub'=>[
            ['label'=>'Pages Information','route'=>'admin.farm.ourfarm'],
            ['label'=>'Farm Experiences','route'=>'admin.farm.experiences']
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

      <div class="px-3 space-y-1">
        @foreach($menuItems as $item)
          @if(!empty($item['sub']))
            @php
              $isAnySubActive = false;
              foreach($item['sub'] as $sub){
                if(request()->routeIs($sub['route'])){
                  $isAnySubActive = true;
                  break;
                }
              }
            @endphp
            <div x-data="{ open: {{ $isAnySubActive ? 'true' : 'false' }} }" class="space-y-1">
              <button
                type="button"
                @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition
                {{ $isAnySubActive ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 shrink-0"></i>
                <span class="truncate">{{ $item['title'] }}</span>
              </div>
              <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform shrink-0"></i>
            </button>

            <div x-cloak x-show="open" x-transition class="pl-6 space-y-1">
              @foreach($item['sub'] as $sub)
                <a
                  href="{{ route($sub['route']) }}"
                  @click="closeSidebar()"
                  class="flex items-center gap-2 px-3 py-2 rounded-lg transition
                  {{ request()->routeIs($sub['route']) ? 'text-yellow-200 bg-white/5' : 'hover:bg-white/5 hover:text-gray-100' }}"
                >
                  <i data-lucide="dot" class="w-4 h-4"></i>
                  <span class="truncate">{{ $sub['label'] }}</span>
                </a>
              @endforeach
            </div>
          </div>
          @else
            <a
              href="{{ route($item['route']) }}"
              @click="closeSidebar()"
              class="flex items-center gap-3 px-3 py-2 rounded-lg transition
              {{ request()->routeIs($item['route']) ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}"
            >
              <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 shrink-0"></i>
              <span class="truncate">{{ $item['title'] }}</span>
            </a>
          @endif
        @endforeach
      </div>
    </nav>

    {{-- Bottom --}}
    <div class="px-6 py-4 border-t border-white/10">
      <a href="{{ route('logout') }}" @click="closeSidebar()" class="flex items-center gap-3 hover:text-gray-200 transition">
        <i data-lucide="log-out" class="w-5 h-5"></i>
        <span>Log Out</span>
      </a>
    </div>
  </aside>

  {{-- Mobile Topbar --}}
  <header class="md:hidden sticky top-0 z-30 bg-gray-100/90 backdrop-blur border-b border-gray-200">
    <div class="px-4 py-3 flex items-center gap-3">
      <button
        type="button"
        @click="openSidebar()"
        class="inline-flex items-center justify-center rounded-lg p-2 bg-[#8f5d54] text-white shadow hover:bg-[#7a4d46] transition"
        aria-label="Open sidebar"
      >
        <i data-lucide="menu" class="w-6 h-6"></i>
      </button>
      <div class="min-w-0">
        <p class="text-sm font-semibold text-gray-900 truncate">@yield('title', 'Admin Panel')</p>
        <p class="text-xs text-gray-500 truncate">Admin Workspace</p>
      </div>
    </div>
  </header>

  {{-- Main --}}
  <main class="admin-main min-h-screen pt-4 md:pt-8 overflow-x-hidden">
    <div class="py-4 md:py-8">
      <div class="w-full mx-auto max-w-screen-2xl px-3 sm:px-4 md:px-6 lg:px-8 2xl:px-10">

        {{-- Flash --}}
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

      </div>
    </div>
  </main>

  {{-- Vendor JS LAST --}}
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (window.lucide) lucide.createIcons();
    });

    document.addEventListener('alpine:init', () => {
      document.addEventListener('alpine:updated', () => {
        if (window.lucide) lucide.createIcons();
      });
    });
  </script>

  @stack('scripts')
</body>
</html>
