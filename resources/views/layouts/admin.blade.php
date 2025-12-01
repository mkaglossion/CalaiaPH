<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin Panel')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
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

      <nav class="mt-4 space-y-1 overflow-y-auto" style="max-height: calc(100vh - 100px);">

        

        <div x-data="{ open: {{ request()->routeIs('admin.do.*') ? 'true' : 'false' }} }" class="space-y-1">
          <button @click="open = !open" 
            class="w-full flex items-center justify-between px-6 py-2 rounded-lg transition
            {{ request()->routeIs('admin.do.*') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}">
            <div class="flex items-center gap-3">
              <i data-lucide="book" class="w-5 h-5"></i>
              <span>Home</span>
            </div>
            <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform"></i>
          </button>

          <div x-cloak x-show="open" x-transition class="pl-10 space-y-1 mt-1">
            <a href="{{ route('admin.do.activities') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.do.activities') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Pages Information</span>
            </a>

            
          </div>
        </div>

        <div x-data="{ open: {{ request()->routeIs('admin.do.*') ? 'true' : 'false' }} }" class="space-y-1">
          <button @click="open = !open" 
            class="w-full flex items-center justify-between px-6 py-2 rounded-lg transition
            {{ request()->routeIs('admin.do.*') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}">
            <div class="flex items-center gap-3">
              <i data-lucide="book" class="w-5 h-5"></i>
              <span>What to Do</span>
            </div>
            <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform"></i>
          </button>

          <div x-cloak x-show="open" x-transition class="pl-10 space-y-1 mt-1">
            <a href="{{ route('admin.do.activities') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.do.activities') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Pages Information</span>

            <a href="{{ route('admin.do.whattodo') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.do.whattodo') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Items</span>
            </a>
            </a>
          </div>
        </div>

        <div x-data="{ open: {{ request()->routeIs('admin.day.*') ? 'true' : 'false' }} }" class="space-y-1">
          <button @click="open = !open" 
            class="w-full flex items-center justify-between px-6 py-2 rounded-lg transition
            {{ request()->routeIs('admin.day.*') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}">
            <div class="flex items-center gap-3">
              <i data-lucide="book" class="w-5 h-5"></i>
              <span>Day Tour</span>
            </div>
            <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform"></i>
          </button>

          <div x-cloak x-show="open" x-transition class="pl-10 space-y-1 mt-1">
            <a href="{{ route('admin.day.daytour') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.day.daytour') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Pages Information</span>

            <a href="{{ route('admin.day.day') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.day.day') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Items</span>
            </a>
            </a>
          </div>
        </div>

        <div x-data="{ open: {{ request()->routeIs('admin.stay.*') ? 'true' : 'false' }} }" class="space-y-1">
          <button @click="open = !open" 
            class="w-full flex items-center justify-between px-6 py-2 rounded-lg transition
            {{ request()->routeIs('admin.stay.*') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}">
            <div class="flex items-center gap-3">
              <i data-lucide="book" class="w-5 h-5"></i>
              <span>Where to Stay</span>
            </div>
            <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform"></i>
          </button>

          <div x-cloak x-show="open" x-transition class="pl-10 space-y-1 mt-1">
            <a href="{{ route('admin.stay.wheretostay') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.stay.wheretostay') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Pages Information</span>

            <a href="{{ route('admin.stay.staycations') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.stay.staycations') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Items</span>
            </a>
            </a>
          </div>
        </div>

        <div x-data="{ open: {{ request()->routeIs('admin.do.*') ? 'true' : 'false' }} }" class="space-y-1">
          <button @click="open = !open" 
            class="w-full flex items-center justify-between px-6 py-2 rounded-lg transition
            {{ request()->routeIs('admin.do.*') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}">
            <div class="flex items-center gap-3">
              <i data-lucide="book" class="w-5 h-5"></i>
              <span>What to Do</span>
            </div>
            <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform"></i>
          </button>

          <div x-cloak x-show="open" x-transition class="pl-10 space-y-1 mt-1">
            <a href="{{ route('admin.do.activities') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.do.activities') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Pages Information</span>

            <a href="{{ route('admin.do.activities') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.do.activities') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Items</span>
            </a>
            </a>
          </div>
        </div>

        <div x-data="{ open: {{ request()->routeIs('admin.do.*') ? 'true' : 'false' }} }" class="space-y-1">
          <button @click="open = !open" 
            class="w-full flex items-center justify-between px-6 py-2 rounded-lg transition
            {{ request()->routeIs('admin.do.*') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}">
            <div class="flex items-center gap-3">
              <i data-lucide="book" class="w-5 h-5"></i>
              <span>Special Events</span>
            </div>
            <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform"></i>
          </button>

          <div x-cloak x-show="open" x-transition class="pl-10 space-y-1 mt-1">
            <a href="{{ route('admin.do.activities') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.do.activities') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Pages Information</span>

            <a href="{{ route('admin.do.activities') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.do.activities') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Items</span>
            </a>
            </a>
          </div>
        </div>

        <div x-data="{ open: {{ request()->routeIs('admin.do.*') ? 'true' : 'false' }} }" class="space-y-1">
          <button @click="open = !open" 
            class="w-full flex items-center justify-between px-6 py-2 rounded-lg transition
            {{ request()->routeIs('admin.do.*') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}">
            <div class="flex items-center gap-3">
              <i data-lucide="book" class="w-5 h-5"></i>
              <span>Our Farm</span>
            </div>
            <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform"></i>
          </button>

          <div x-cloak x-show="open" x-transition class="pl-10 space-y-1 mt-1">
            <a href="{{ route('admin.do.activities') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.do.activities') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Pages Information</span>

            <a href="{{ route('admin.do.activities') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.do.activities') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Items</span>
            </a>
            </a>
          </div>
        </div>

        {{-- Pages Dropdown
        <div x-data="{ open: {{ request()->routeIs('admin.pages.*') ? 'true' : 'false' }} }" class="space-y-1">
          <button @click="open = !open" 
            class="w-full flex items-center justify-between px-6 py-2 rounded-lg transition
            {{ request()->routeIs('admin.pages.*') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}">
            <div class="flex items-center gap-3">
              <i data-lucide="book" class="w-5 h-5"></i>
              <span>Pages</span>
            </div>
            <i :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform"></i>
          </button>

          <div x-cloak x-show="open" x-transition class="pl-10 space-y-1 mt-1">
            <a href="{{ route('admin.home.home') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.home.home') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="home" class="w-4 h-4"></i> <span>Home</span>
            </a>
            <a href="{{ route('admin.day.daytour') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.day.daytour') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="sun" class="w-4 h-4"></i> <span>Day Tour</span>
            </a>
            <a href="{{ route('admin.stay.wheretostay') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.stay.wheretostay') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="building-2" class="w-4 h-4"></i> <span>Where to Stay</span>
            </a>
            <a href="{{ route('admin.do.whattodo') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.do.whattodo') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="mountain" class="w-4 h-4"></i> <span>What to Do</span>
            </a>
            <a href="{{ route('admin.pages.foodandbeverage') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.pages.foodandbeverage') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="utensils" class="w-4 h-4"></i> <span>Food & Beverage</span>
            </a>
            <a href="{{ route('admin.pages.specialevents') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.pages.specialevents') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="party-popper" class="w-4 h-4"></i> <span>Special Events</span>
            </a>
            <a href="{{ route('admin.pages.ourfarm') }}" class="flex items-center gap-2 py-1 transition
              {{ request()->routeIs('admin.pages.ourfarm') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
              <i data-lucide="leaf" class="w-4 h-4"></i> <span>Our Farm</span>
            </a>
          </div>
        </div> --}}

        {{-- Reports --}}
        <a class="flex items-center gap-3 px-6 py-2 rounded-lg transition
          {{ request()->routeIs('admin.reports') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]/80' }}">
          <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
          <span>Reports</span>
        </a>

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
    @yield('content')
  </main>

  <script>
    lucide.createIcons();
  </script>
</body>
</html>
