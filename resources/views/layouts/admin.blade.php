<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin Panel')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
</head>

<body x-data="{ sidebarOpen: false, pageMenu: false }" class="bg-gray-100 flex min-h-screen">

  {{-- Sidebar --}}
  <aside 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-64 md:translate-x-0'"
    class="fixed md:static md:flex flex-col justify-between w-64 bg-[#8f5d54] text-white min-h-screen transform transition-transform duration-300 z-50">

    {{-- Top Section --}}
    <div>
      <div class="flex items-center justify-between px-6 py-4 border-b border-[#8f5d54]/50">
        <h1 class="text-2xl font-bold tracking-wide">Admin Panel</h1>
        <button @click="sidebarOpen = false" class="md:hidden">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <nav class="mt-4 space-y-1">

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}" 
          class="flex items-center gap-3 px-6 py-2 transition 
          {{ request()->routeIs('admin.dashboard') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]' }}">
          <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
          <span>Dashboard</span>
        </a>

        {{-- Manage Users --}}
        <a href="{{ route('admin.users') }}" 
          class="flex items-center gap-3 px-6 py-2 transition 
          {{ request()->routeIs('admin.users') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]' }}">
          <i data-lucide="users" class="w-5 h-5"></i>
          <span>Manage Users</span>
        </a>

        {{-- Bookings --}}
        <a href="{{ route('admin.bookings') }}" 
          class="flex items-center gap-3 px-6 py-2 transition 
          {{ request()->routeIs('admin.bookings') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]' }}">
          <i data-lucide="calendar-days" class="w-5 h-5"></i>
          <span>Bookings</span>
        </a>

        {{-- Staycations --}}
        <a href="{{ route('admin.staycations') }}" 
          class="flex items-center gap-3 px-6 py-2 transition 
          {{ request()->routeIs('admin.staycations') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]' }}">
          <i data-lucide="bed-double" class="w-5 h-5"></i>
          <span>Staycations</span>
        </a>

        {{-- Activities --}}
        <a href="{{ route('admin.activities') }}" 
          class="flex items-center gap-3 px-6 py-2 transition 
          {{ request()->routeIs('admin.activities') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]' }}">
          <i data-lucide="mountain" class="w-5 h-5"></i>
          <span>Activities</span>
        </a>

        {{-- Pages (Dropdown) --}}
        <div 
        class="space-y-1"
        x-data="{ pageMenu: {{ request()->routeIs('admin.pages.*') ? 'true' : 'false' }} }"
        >
       <button 
            @click="pageMenu = !pageMenu"
            class="w-full flex items-center justify-between px-6 py-2 transition 
            {{ request()->routeIs('admin.pages.*') 
                ? 'bg-[#7a4d46] font-semibold' 
                : 'hover:bg-[#7a4d46]' }}"
        >
            <div class="flex items-center gap-3 ">
            <i data-lucide="book" class="w-5 h-5"></i>
            <span>Pages</span>
            </div>
            <i :class="pageMenu ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform"></i>
        </button>

        <div 
            x-cloak 
            x-show="pageMenu" 
            x-transition 
            class="pl-10 space-y-1"
        >
            <a href="{{ route('admin.pages.home') }}" 
            class="flex items-center gap-2 py-1 transition 
            {{ request()->routeIs('admin.pages.home') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
            <i data-lucide="home" class="w-4 h-4"></i>
            <span>Home</span>
            </a>
            <a href="{{ route('admin.pages.daytour') }}" 
            class="flex items-center gap-2 py-1 transition 
            {{ request()->routeIs('admin.pages.daytour') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
            <i data-lucide="sun" class="w-4 h-4"></i>
            <span>Day Tour</span>
            </a>
            <a href="{{ route('admin.pages.wheretostay') }}" 
            class="flex items-center gap-2 py-1 transition 
            {{ request()->routeIs('admin.pages.wheretostay') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
            <i data-lucide="building-2" class="w-4 h-4"></i>
            <span>Where to Stay</span>
            </a>
            <a href="{{ route('admin.pages.whattodo') }}" 
            class="flex items-center gap-2 py-1 transition 
            {{ request()->routeIs('admin.pages.whattodo') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
            <i data-lucide="mountain" class="w-4 h-4"></i>
            <span>What to Do</span>
            </a>
            <a href="{{ route('admin.pages.foodandbeverage') }}" 
            class="flex items-center gap-2 py-1 transition 
            {{ request()->routeIs('admin.pages.foodandbeverage') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
            <i data-lucide="utensils" class="w-4 h-4"></i>
            <span>Food & Beverage</span>
            </a>
            <a href="{{ route('admin.pages.specialevents') }}" 
            class="flex items-center gap-2 py-1 transition 
            {{ request()->routeIs('admin.pages.specialevents') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
            <i data-lucide="party-popper" class="w-4 h-4"></i>
            <span>Special Events</span>
            </a>
            <a href="{{ route('admin.pages.ourfarm') }}" 
            class="flex items-center gap-2 py-1 transition 
            {{ request()->routeIs('admin.pages.ourfarm') ? 'text-yellow-200' : 'hover:text-gray-300' }}">
            <i data-lucide="leaf" class="w-4 h-4"></i>
            <span>Our Farm</span>
            </a>
        </div>
        </div>


        {{-- Reports --}}
        <a href="{{ route('admin.reports') }}" 
          class="flex items-center gap-3 px-6 py-2 transition 
          {{ request()->routeIs('admin.reports') ? 'bg-[#7a4d46] font-semibold' : 'hover:bg-[#7a4d46]' }}">
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
  <main class="flex-1 p-8">
    @yield('content')
  </main>

  <script>
    lucide.createIcons();
  </script>
</body>
</html>
