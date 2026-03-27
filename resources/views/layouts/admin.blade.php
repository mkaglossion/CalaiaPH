<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv="Permissions-Policy" content="unload=(self), fullscreen=(self), geolocation=(self), microphone=(self), camera=(self), display-capture=(self)">
  <title>@yield('title', 'Admin Panel')</title>

  @include('layouts.partials.favicon')

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet" crossorigin>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" integrity="sha256-GzSkJVLJbxDk36qko2cnawOGiqz/Y8GsQv/jMTUrx1Q=" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11.0.5/swiper-bundle.min.css" integrity="sha256-yUoNxsvX+Vo8Trj3lZ/Y5ZBf8HlBFsB6Xwm7rH75/9E=" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

  @vite(['resources/js/app.js'])

  <style>
    [x-cloak] { display: none !important; }

    :root { --sidebar-w: 18rem; }
    @media (min-width: 1024px) { :root { --sidebar-w: 17rem; } }
    @media (min-width: 1280px) { :root { --sidebar-w: 18rem; } }
    @media (min-width: 1536px) { :root { --sidebar-w: 20rem; } }

    @media (min-width: 768px) {
      .admin-main { padding-left: var(--sidebar-w); }
      .admin-header { left: var(--sidebar-w); }
    }
  </style>
</head>

<body
  x-data="{
    sidebarOpen: false,
    closeSidebar() { this.sidebarOpen = false },
    openSidebar() { this.sidebarOpen = true }
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

      <button
        type="button"
        @click="closeSidebar()"
        class="md:hidden inline-flex items-center justify-center rounded-lg p-2 hover:bg-white/10 transition"
        aria-label="Close sidebar"
      >
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    {{-- Nav --}}
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
            ['label'=>'Unified Inventory','route'=>'admin.bookings.inventory'],
          ]],
          ['title'=>'Reservation Monitor','icon'=>'bell-ring','route'=>'admin.reservations.*','sub'=>[
            ['label'=>'Monitor','route'=>'admin.reservations.monitor'],
          ]],
          ['title'=>'Day Tour','icon'=>'sun','route'=>'admin.day.*','sub'=>[
            ['label'=>'Pages Information','route'=>'admin.day.daytour'],
            ['label'=>'Items','route'=>'admin.day.day'],
          ]],
          ['title'=>'Packages','icon'=>'package','route'=>'admin.packages.*','sub'=>[
            ['label'=>'All Packages','route'=>'admin.packages.index'],
            ['label'=>'Page Information','route'=>'admin.packages.packagesInfos'],
          ]],
          ['title'=>'Where to Stay','icon'=>'building','route'=>'admin.stay.*','sub'=>[
            ['label'=>'Pages Information','route'=>'admin.stay.wheretostay'],
            ['label'=>'Variants','route'=>'admin.stay.staycations'],
          ]],
          ['title'=>'What to Do','icon'=>'map-pin','route'=>'admin.do.*','sub'=>[
            ['label'=>'Pages Information','route'=>'admin.do.whattodo'],
            ['label'=>'Items','route'=>'admin.do.activities']
          ]],
          ['title'=>'Food & Beverage','icon'=>'utensils-crossed','route'=>'admin.food.*','sub'=>[
            ['label'=>'Pages Information','route'=>'admin.food.foodandbeverage'],
            ['label'=>'Category Images','route'=>'admin.dining.category-images.index'],
            ['label'=>'Food Management','route'=>'admin.foods.index'],
          ]],
          ['title'=>'Special Events','icon'=>'star','route'=>'admin.specialEvents.*','sub'=>[
            ['label'=>'Pages Information','route'=>'admin.specialEvents.specialevents'],
            ['label'=>'Manager','route'=>'admin.specialEvents.manager'],
          ]],
          ['title'=>'Our Farm','icon'=>'leaf','route'=>'admin.farm.*','sub'=>[
            ['label'=>'Pages Information','route'=>'admin.farm.ourfarm'],
            ['label'=>'Farm Experiences','route'=>'admin.farm.experiences']
          ]],
          ['title'=>'Shop','icon'=>'shopping-bag','route'=>'admin.shop.*','sub'=>[
            ['label'=>'Pages Information','route'=>'admin.shop.shopstore'],
            ['label'=>'Store Management','route'=>'admin.shop.shopitems'],
            ['label'=>'Inventory Monitor','route'=>'admin.inventory.shop']
          ]],
          ['title'=>'Pricing','icon'=>'coins','route'=>'admin.pricing.*','sub'=>[
            ['label'=>'Rate Matrix','route'=>'admin.pricingConfig.index'],
          ]],
          ['title'=>'Settings','icon'=>'settings','route'=>'admin.settings.*','sub'=>[
            ['label'=>'Profile','route'=>'admin.settings.profile'],
            ['label'=>'Change Password','route'=>'admin.settings.password'],
            ['label'=>'Activity Log','route'=>'admin.settings.activity-log'],
            ['label'=>'System Settings','route'=>'admin.settings.system'],
            ['label'=>'Alerts','route'=>'admin.settings.alerts'],
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
                @click="open = !open; setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 0)"
                class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition {{ $isAnySubActive ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}"
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
                    class="flex items-center gap-2 px-3 py-2 rounded-lg transition {{ request()->routeIs($sub['route']) ? 'text-yellow-200 bg-white/5' : 'hover:bg-white/5 hover:text-gray-100' }}"
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
              class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs($item['route']) ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}"
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

      <div class="min-w-0 flex-1">
        <p class="text-sm font-semibold text-gray-900 truncate">@yield('title', 'Admin Panel')</p>
        <p class="text-xs text-gray-500 truncate">Admin Workspace</p>
      </div>
      <!-- Mobile Search Button -->
      <button
        type="button"
        @click="document.getElementById('mobileSearchModal').style.display='block'; setTimeout(() => { document.querySelector('#mobile-search-input')?.focus(); }, 10);"
        class="inline-flex items-center justify-center rounded-lg p-2 hover:bg-gray-200 transition"
        aria-label="Search"
      >
        <i class="fas fa-magnifying-glass w-5 h-5 text-gray-600"></i>
      </button>
    </div>
  </header>

  {{-- Desktop Header --}}
  <header class="admin-header hidden md:block fixed top-0 right-0 h-16 bg-white border-b border-gray-200 z-40">
    <div class="h-full px-6 flex items-center justify-between">
      <div></div>

      <div class="flex items-center gap-4">
        {{-- Search --}}
        <div class="hidden lg:block">
          <div class="relative" x-data="searchBox()">
            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
            <input
              id="admin-search"
              name="admin_search"
              type="text"
              placeholder="Search bookings, users..."
              x-model="query"
              @input="search()"
              @focus="showResults = true"
              @blur="setTimeout(() => showResults = false, 200)"
              class="w-64 pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
            
            <!-- Search Results Dropdown -->
            <div 
              x-show="showResults && results.length > 0"
              x-cloak
              class="absolute top-full left-0 right-0 mt-2 bg-white rounded-lg shadow-lg border border-gray-200 z-50 max-h-96 overflow-y-auto"
            >
              <template x-for="result in results" :key="result.id">
                <a
                  :href="result.url"
                  class="block px-4 py-3 hover:bg-gray-50 transition border-b border-gray-100 last:border-0"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900" x-text="result.title"></p>
                      <p class="text-xs text-gray-500" x-text="result.subtitle"></p>
                    </div>
                    <div class="flex items-center gap-2">
                      <span 
                        class="text-xs px-2 py-1 rounded font-medium"
                        :class="{
                          'bg-blue-100 text-blue-700': result.type === 'user',
                          'bg-green-100 text-green-700': result.type === 'booking'
                        }"
                        x-text="result.type === 'user' ? 'User' : 'Booking'"
                      ></span>
                      <span 
                        x-show="result.status"
                        class="text-xs px-2 py-1 rounded font-medium"
                        :class="{
                          'bg-yellow-100 text-yellow-700': result.status === 'pending',
                          'bg-green-100 text-green-700': result.status === 'confirmed',
                          'bg-red-100 text-red-700': result.status === 'cancelled'
                        }"
                        x-text="result.status ? result.status.charAt(0).toUpperCase() + result.status.slice(1) : ''"
                      ></span>
                    </div>
                  </div>
                </a>
              </template>
            </div>
            
            <!-- No Results -->
            <div
              x-show="showResults && results.length === 0 && query.length > 0"
              x-cloak
              class="absolute top-full left-0 right-0 mt-2 bg-white rounded-lg shadow-lg border border-gray-200 z-50 p-4 text-center text-gray-500 text-sm"
            >
              No results found
            </div>
          </div>
        </div>

        {{-- Alerts --}}
        <div x-data="{ alertsOpen: false }" class="relative">
          <button
            type="button"
            @click="alertsOpen = !alertsOpen"
            class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition"
          >
            <i data-lucide="circle-alert" class="w-5 h-5"></i>
            @php
              $unreadAlerts = \App\Models\AdminSystemAlert::unresolvedCritical()->count();
            @endphp
            @if($unreadAlerts > 0)
              <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            @endif
          </button>

          <div
            x-cloak
            x-show="alertsOpen"
            @click.outside="alertsOpen = false"
            x-transition
            class="absolute top-full right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
          >
            <div class="p-4 border-b border-gray-100">
              <h3 class="font-semibold text-gray-900">System Alerts</h3>
            </div>

            <div class="max-h-64 overflow-y-auto">
              @php
                $alerts = \App\Models\AdminSystemAlert::unresolvedCritical()->limit(5)->get();
              @endphp

              @forelse($alerts as $alert)
                <div class="p-4 border-b border-gray-100 last:border-0 hover:bg-gray-50 cursor-pointer">
                  <div class="flex items-start gap-2">
                    <span
                      class="inline-block w-2 h-2 rounded-full mt-1"
                      style="background-color: {{ $alert->severity === 'critical' ? '#dc2626' : '#f59e0b' }};"
                    ></span>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $alert->type)) }}</p>
                      <p class="text-xs text-gray-500 mt-1">{{ $alert->message }}</p>
                    </div>
                  </div>
                </div>
              @empty
                <div class="p-4 text-center text-gray-500 text-sm">
                  No critical alerts
                </div>
              @endforelse
            </div>

            <div class="p-3 border-t border-gray-100">
              <a href="{{ route('admin.settings.alerts') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                View All Alerts →
              </a>
            </div>
          </div>
        </div>

        {{-- Profile --}}
        <div x-data="{ profileOpen: false }" class="relative">
          <button
            type="button"
            @click="profileOpen = !profileOpen"
            @keydown.escape="profileOpen = false"
            class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition focus:outline-none focus:ring-2 focus:ring-blue-500"
            aria-haspopup="menu"
            :aria-expanded="profileOpen"
          >
            <div class="relative w-8 h-8">
              <div class="w-8 h-8 rounded-full bg-[#8f5d54] text-white flex items-center justify-center text-sm font-semibold">
                {{ substr(Auth::user()->first_name, 0, 1) }}
              </div>
              <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-white"></span>
            </div>

            <span class="text-sm font-medium text-gray-700 hidden sm:inline">
              {{ Auth::user()->first_name }}
            </span>

            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-600"></i>
          </button>

          <div
            x-cloak
            x-show="profileOpen"
            @click.outside="profileOpen = false"
            @keydown.escape="profileOpen = false"
            x-transition
            role="menu"
            class="absolute top-full right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-0 z-50"
          >
            <a href="{{ route('admin.settings.profile') }}" role="menuitem" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 transition text-sm focus:outline-none focus:bg-gray-100 pt-2 rounded-t-lg">
              <i data-lucide="user" class="w-4 h-4"></i>
              My Profile
            </a>

            <a href="{{ route('admin.settings.password') }}" role="menuitem" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 transition text-sm focus:outline-none focus:bg-gray-100">
              <i data-lucide="lock" class="w-4 h-4"></i>
              Change Password
            </a>

            <a href="{{ route('admin.settings.activity-log') }}" role="menuitem" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 transition text-sm focus:outline-none focus:bg-gray-100">
              <i data-lucide="history" class="w-4 h-4"></i>
              Activity Log
            </a>

            <a href="{{ route('admin.settings.system') }}" role="menuitem" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 transition text-sm focus:outline-none focus:bg-gray-100">
              <i data-lucide="settings" class="w-4 h-4"></i>
              Settings
            </a>

            <div class="border-t border-gray-200 my-1"></div>

            <a href="{{ route('admin.help') }}" role="menuitem" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 transition text-sm text-blue-600 focus:outline-none focus:bg-gray-100">
              <i data-lucide="help-circle" class="w-4 h-4"></i>
              Help & Docs
            </a>

            <div class="border-t border-gray-200 my-1"></div>

            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-[#8f5d54] text-white flex items-center justify-center text-sm font-semibold shrink-0">
                  {{ substr(Auth::user()->first_name, 0, 1) }}
                </div>
                <div class="flex flex-col min-w-0">
                  <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</p>
                  <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                </div>
              </div>
            </div>

            <a href="{{ route('logout') }}" role="menuitem" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 transition text-sm text-red-600 focus:outline-none focus:bg-gray-100 rounded-b-lg pb-2">
              <i data-lucide="log-out" class="w-4 h-4"></i>
              Log Out
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

  {{-- Main --}}
  <main class="admin-main min-h-screen pt-4 md:pt-20 overflow-x-hidden">
    <div class="py-4 md:py-8">
      <div class="w-full mx-auto max-w-screen-2xl px-3 sm:px-4 md:px-6 lg:px-8 2xl:px-10">
        @if (session('error'))
          <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
              <i data-lucide="x" class="w-4 h-4"></i>
            </button>
          </div>
        @endif

        @yield('content')
      </div>
    </div>
  </main>

  {{-- Mobile Search Modal --}}
  <div 
    id="mobileSearchModal"
    class="fixed inset-0 bg-black/50 z-50 md:hidden"
    x-data="mobileSearch()"
    @click.self="closeMobileSearch()"
    style="display: none;"
  >
    <div class="fixed inset-x-0 top-0 bg-white border-b border-gray-200 rounded-b-lg shadow-lg">
      <!-- Search Header -->
      <div class="px-4 py-4 flex items-center gap-3">
        <button
          type="button"
          @click="closeMobileSearch()"
          class="text-gray-400 hover:text-gray-600"
          aria-label="Close search"
        >
          <i class="fas fa-chevron-left w-6 h-6"></i>
        </button>
        
        <div class="flex-1 relative">
          <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
          <input
            id="mobile-search-input"
            type="text"
            placeholder="Search..."
            x-model="query"
            @input="search()"
            @keydown.down="moveDown()"
            @keydown.up="moveUp()"
            @keydown.enter="selectResult()"
            @keydown.escape="closeMobileSearch()"
            class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            x-ref="mobileInput"
          >
        </div>

        <!-- Clear Button -->
        <button
          x-show="query.length > 0"
          @click="clearSearch()"
          class="text-gray-400 hover:text-gray-600"
          type="button"
          aria-label="Clear search"
        >
          <i class="fas fa-xmark w-5 h-5"></i>
        </button>
      </div>

      <!-- Filter Tabs -->
      <div x-show="results.length > 0" class="flex gap-2 px-4 py-3 border-t border-gray-100 overflow-x-auto">
        <button @click="selectedFilter = 'all'" :class="selectedFilter === 'all' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'" class="text-xs px-3 py-1 rounded-full font-medium transition whitespace-nowrap" type="button">All</button>
        <button @click="selectedFilter = 'booking'" :class="selectedFilter === 'booking' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'" class="text-xs px-3 py-1 rounded-full font-medium transition whitespace-nowrap" type="button">Bookings</button>
        <button @click="selectedFilter = 'user'" :class="selectedFilter === 'user' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'" class="text-xs px-3 py-1 rounded-full font-medium transition whitespace-nowrap" type="button">Users</button>
      </div>

      <!-- Results List -->
      <div class="max-h-96 overflow-y-auto">
        <template x-for="(result, index) in filteredResults" :key="result.id">
          <a
            :href="result.url"
            @click="closeMobileSearch(); addToRecentSearches(query)"
            :class="selectedIndex === index ? 'bg-blue-50' : 'active:bg-gray-100'"
            class="flex items-center justify-between px-4 py-3 border-b border-gray-100 last:border-0"
          >
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900" x-text="result.title"></p>
              <p class="text-xs text-gray-500" x-text="result.subtitle"></p>
            </div>
            <div class="flex items-center gap-2">
              <span :class="{'bg-blue-100 text-blue-700': result.type === 'user', 'bg-green-100 text-green-700': result.type === 'booking'}" class="text-xs px-2 py-1 rounded font-medium" x-text="result.type === 'user' ? 'User' : 'Booking'"></span>
              <span x-show="result.status" :class="{'bg-yellow-100 text-yellow-700': result.status === 'pending', 'bg-green-100 text-green-700': result.status === 'confirmed', 'bg-red-100 text-red-700': result.status === 'cancelled'}" class="text-xs px-2 py-1 rounded font-medium" x-text="result.status ? result.status.charAt(0).toUpperCase() + result.status.slice(1) : ''"></span>
            </div>
          </a>
        </template>

        <!-- Recent Searches -->
        <template x-if="query.length === 0 && recentSearches.length > 0">
          <div>
            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 border-t border-gray-100">Recent</div>
            <template x-for="search in recentSearches.slice(0, 5)" :key="search">
              <button
                @click="query = search; search()"
                class="w-full text-left px-4 py-3 hover:bg-gray-50 transition text-sm text-gray-700 border-b border-gray-100 last:border-0"
                type="button"
              >
                <i class="fas fa-clock text-gray-400 mr-2 w-4"></i>
                <span x-text="search"></span>
              </button>
            </template>
          </div>
        </template>

        <!-- No Results -->
        <div x-show="results.length === 0 && query.length > 0" class="p-8 text-center">
          <i class="fas fa-search text-gray-300 text-3xl mb-2"></i>
          <p class="text-gray-700 font-medium mb-1">No results found</p>
          <p class="text-xs text-gray-500">Try searching by booking ID, customer name, email, or user name</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Vendor JS --}}
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

  <script>
    toastr.options = {
      closeButton: true,
      debug: false,
      newestOnTop: true,
      progressBar: true,
      positionClass: "toast-top-right",
      preventDuplicates: false,
      onclick: null,
      showDuration: "300",
      hideDuration: "1000",
      timeOut: "5000",
      extendedTimeOut: "1000",
      showEasing: "swing",
      hideEasing: "linear",
      showMethod: "fadeIn",
      hideMethod: "fadeOut"
    };

    function initLucideIcons() {
      if (window.lucide) {
        lucide.createIcons();
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      initLucideIcons();
    });

    document.addEventListener('alpine:init', () => {
      document.addEventListener('alpine:updated', () => {
        setTimeout(() => {
          initLucideIcons();
        }, 0);
      });
    });

    // Search Box Component
    function searchBox() {
      return {
        query: '',
        results: [],
        showResults: false,
        searchTimeout: null,
        
        search() {
          clearTimeout(this.searchTimeout);
          
          if (this.query.length < 2) {
            this.results = [];
            return;
          }
          
          this.searchTimeout = setTimeout(() => {
            fetch(`{{ route('admin.api.search') }}?q=${encodeURIComponent(this.query)}`)
              .then(res => res.json())
              .then(data => {
                this.results = data.results;
              })
              .catch(err => console.error('Search error:', err));
          }, 300);
        }
      };
    }

    // Mobile Search Component
    function mobileSearch() {
      return {
        query: '',
        results: [],
        recentSearches: [],
        selectedFilter: 'all',
        selectedIndex: -1,
        searchTimeout: null,

        init() {
          const stored = localStorage.getItem('mobileRecentSearches');
          this.recentSearches = stored ? JSON.parse(stored) : [];
        },

        get filteredResults() {
          if (this.selectedFilter === 'all') return this.results;
          return this.results.filter(r => r.type === this.selectedFilter);
        },

        search() {
          clearTimeout(this.searchTimeout);
          this.selectedIndex = -1;
          
          if (this.query.length < 2) {
            this.results = [];
            return;
          }
          
          this.searchTimeout = setTimeout(() => {
            fetch(`{{ route('admin.api.search') }}?q=${encodeURIComponent(this.query)}`)
              .then(res => res.json())
              .then(data => {
                this.results = data.results;
              })
              .catch(err => console.error('Search error:', err));
          }, 300);
        },

        clearSearch() {
          this.query = '';
          this.results = [];
          this.selectedIndex = -1;
          this.$refs.mobileInput?.focus();
        },

        closeMobileSearch() {
          document.getElementById('mobileSearchModal').style.display = 'none';
          this.query = '';
          this.results = [];
          this.selectedIndex = -1;
          this.selectedFilter = 'all';
        },

        addToRecentSearches(term) {
          if (!term.trim()) return;
          this.recentSearches = [term, ...this.recentSearches.filter(s => s !== term)].slice(0, 10);
          localStorage.setItem('mobileRecentSearches', JSON.stringify(this.recentSearches));
        },

        moveDown() {
          const max = this.filteredResults.length - 1;
          if (this.selectedIndex < max) {
            this.selectedIndex++;
          }
        },

        moveUp() {
          if (this.selectedIndex > 0) {
            this.selectedIndex--;
          } else {
            this.selectedIndex = -1;
          }
        },

        selectResult() {
          if (this.selectedIndex >= 0 && this.filteredResults[this.selectedIndex]) {
            const result = this.filteredResults[this.selectedIndex];
            this.addToRecentSearches(this.query);
            window.location.href = result.url;
          }
        }
      };
    }
  </script>

  @stack('scripts')
</body>
</html>