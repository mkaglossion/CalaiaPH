@props(['active' => 'home'])

@php
   $cart = session('cart', []);
   $cartCount = 0;

   if (is_array($cart)) {
       foreach ($cart as $item) {
           $cartCount += $item['quantity'] ?? 0;
       }
   }
@endphp

<header
  x-data="{ open: false }"
  x-init="
    Alpine.store('modals', { showLogin: false, showSignup: false, signupSuccess: false, showCart: false });
    $watch('open', value => document.body.style.overflow = value ? 'hidden' : '')
  "
  class="bg-[#f6f0eb] text-[#8f5d54] shadow-lg sticky top-0 z-50"
>

  <div class="container mx-auto flex items-center justify-between py-4 px-4 md:py-6 md:px-6 text-center">

    {{-- MOBILE LOGO LEFT --}}
    <div class="md:hidden">
      <a href="{{ url('/') }}">
        <div class="bg-[#f6f0eb] w-20 h-20 rounded-full flex items-center justify-center shadow-md overflow-hidden">
          <img
            src="{{ asset('assets/logo/CALAIA LOGO RUST.png') }}"
            alt="CALAIA LOGO RUST"
            class="w-[70%] h-[70%] object-contain"
          >
        </div>
      </a>
    </div>

    {{-- DESKTOP LOGO --}}
    <div x-show="!$store.modals.showLogin && !$store.modals.showSignup" class="hidden md:block fixed -top-4 -left-4 z-50">
      <a href="{{ url('/') }}">
        <div class="bg-[#f6f0eb] w-56 h-56 rounded-full flex items-center justify-center shadow-md overflow-hidden">
          <img
            src="{{ asset('assets/logo/CALAIA LOGO RUST.png') }}"
            alt="CALAIA LOGO RUST"
            class="w-[65%] h-[65%] object-contain"
          >
        </div>
      </a>
    </div>

    {{-- MAIN NAV CENTER --}}
    <nav class="hidden md:flex flex-1 justify-center space-x-6 text-center ml-45">
      <a href="{{ url('/') }}" class="text-[#8f5d54] hover:text-[#8f5d54]/80 font-medium transition-all duration-150 uppercase {{ $active === 'home' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">Home</a>
      <a href="{{ url('/daytour') }}" class="text-[#8f5d54] hover:text-[#8f5d54]/80 font-medium transition-all duration-150 uppercase {{ $active === 'daytour' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">Day Tour</a>
      <a href="{{ url('/staycation') }}" class="text-[#8f5d54] hover:text-[#8f5d54]/80 font-medium transition-all duration-150 uppercase {{ $active === 'staycation' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">Where to Stay</a>
      <a href="{{ url('/activities') }}" class="text-[#8f5d54] hover:text-[#8f5d54]/80 font-medium transition-all duration-150 uppercase {{ $active === 'activities' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">What to Do</a>
      <a href="{{ url('/dining') }}" class="text-[#8f5d54] hover:text-[#8f5d54]/80 font-medium transition-all duration-150 uppercase {{ $active === 'dining' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">Food & Beverage</a>
      <a href="{{ url('/special-events') }}" class="text-[#8f5d54] hover:text-[#8f5d54]/80 font-medium transition-all duration-150 uppercase {{ $active === 'special-events' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">Special Events</a>
      <a href="{{ url('/farm') }}" class="text-[#8f5d54] hover:text-[#8f5d54]/80 font-medium transition-all duration-150 uppercase {{ $active === 'farm' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">Our Farm</a>
    </nav>

    {{-- DESKTOP USER / GUEST BUTTONS RIGHT --}}
    <nav class="hidden md:flex items-center space-x-4 relative" x-data="{ open: false }">
      @auth
        <!-- User Menu -->
        <div class="relative inline-block text-left">
          <button
            @click="open = !open"
            class="flex items-center space-x-2 focus:outline-none"
          >
            <svg xmlns="http://www.w3.org/2000/svg"
               fill="none" viewBox="0 0 24 24"
               stroke-width="2" stroke="currentColor"
               class="w-6 h-6 text-[#8f5d54]">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 1115 0v.75H4.5v-.75z" />
           </svg>
          </button>

          <!-- Dropdown Menu -->
          <div
            x-show="open"
            @click.away="open = false"
            x-transition
            class="absolute right-0 mt-2 w-48 lg:w-55 bg-white border border-gray-200 rounded-lg shadow-lg z-50"
          >
            <a
              href="{{ route('profile.show') }}"
              class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
              <i class="bi bi-person me-2"></i>My Profile
            </a>

            <a
              href="/booking/staycation"
              class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
              <i class="bi bi-calendar-check me-2"></i>Book Now
            </a>

            <a
              href="{{ route('booking.history') }}"
              class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
              <i class="bi bi-calendar-event me-2"></i>My Bookings
            </a>

            @if (Auth::user()->user_type === 'admin')
              <a
                href="{{ route('admin.dashboard') }}"
                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                 <i class="bi bi-gear me-2"></i>Go to Admin Panel
              </a>
            @endif

            <a
              href="{{ route('checkinout') }}"
              class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
              <i class="bi bi-person-check me-2"></i>Check-in / Check-out
            </a>

            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button
                type="submit"
                class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                <i class="bi bi-box-arrow-right me-2"></i>Log Out
              </button>
            </form>
          </div>
        </div>

        {{-- CART BUTTON (DESKTOP) --}}
        <x-cart />
      @else
        <!-- Guest Buttons -->
        <button
          @click="$store.modals.showLogin = true"
          class="text-[#8f5d54] hover:text-[#8f5d54]/80 font-medium transition-all duration-150"
          style="">
          Log In
        </button>
        <button
          @click="$store.modals.showSignup = true"
          class="bg-[#8f5d54] text-white px-5 py-2 rounded-lg font-semibold hover:bg-[#8f5d54]/90 transition-all shadow-md"
          style="">
          Sign Up
        </button>

        {{-- CART BUTTON (DESKTOP) --}}
        <x-cart />
      @endauth
    </nav>

    {{-- MOBILE MENU BUTTON --}}
    <div class="md:hidden">
      <button @click="open = !open" class="text-[#8f5d54] p-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
      <div
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="fixed top-16 left-0 right-0 bg-white px-4 py-2 z-50 shadow-lg text-left"
      >
        <a href="{{ url('/') }}" @click="open = false" class="block py-2 text-[#8f5d54] uppercase {{ $active === 'home' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">Home</a>
        <a href="{{ url('/daytour') }}" @click="open = false" class="block py-2 text-[#8f5d54] uppercase {{ $active === 'daytour' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">Day Tour</a>
        <a href="{{ url('/staycation') }}" @click="open = false" class="block py-2 text-[#8f5d54] uppercase {{ $active === 'staycation' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">Where to Stay</a>
        <a href="{{ url('/activities') }}" @click="open = false" class="block py-2 text-[#8f5d54] uppercase {{ $active === 'activities' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">What to Do</a>
        <a href="{{ url('/dining') }}" @click="open = false" class="block py-2 text-[#8f5d54] uppercase {{ $active === 'dining' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">Food & Beverage</a>
        <a href="{{ url('/special-events') }}" @click="open = false" class="block py-2 text-[#8f5d54] uppercase {{ $active === 'special-events' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">Special Events</a>
        <a href="{{ url('/farm') }}" @click="open = false" class="block py-2 text-[#8f5d54] uppercase {{ $active === 'farm' ? 'border-b-2 border-[#8f5d54]' : '' }}" style="">Our Farm</a>

        <a href="{{ route('cart.index') }}" data-js-cart-button
           @click="open = false"
           class="flex items-center justify-between py-2 text-[#8f5d54] uppercase"
           style="">
          <span>Cart</span>
          @if($cartCount > 0)
            <span data-js-cart-count class="inline-flex items-center justify-center min-w-5 h-5 rounded-full bg-[#8f5d54] text-white text-xs">
              {{ $cartCount }}
            </span>
          @endif
        </a>

        @auth
          <a href="{{ route('profile.show') }}" @click="open = false" class="block py-2 text-gray-700 border-t border-[#8f5d54]/20 mt-2 pt-2">
            <i class="bi bi-person me-2"></i>Profile
          </a>
          <a href="/booking/staycation" @click="open = false" class="block py-2 text-gray-700">
            <i class="bi bi-calendar-check me-2"></i>Book Now
          </a>
          <a href="{{ route('booking.history') }}" @click="open = false" class="block py-2 text-gray-700">
            <i class="bi bi-calendar-event me-2"></i>My Bookings
          </a>
          @if (Auth::user()->user_type === 'admin')
            <a href="{{ route('admin.dashboard') }}" @click="open = false" class="block py-2 text-gray-700">
              <i class="bi bi-gear me-2"></i>Admin Panel
            </a>
          @endif
          <a href="{{ route('checkinout') }}" @click="open = false" class="block py-2 text-gray-700">
            <i class="bi bi-person-check me-2"></i>Check-in / Check-out
          </a>
          <form method="POST" action="{{ route('logout') }}" @submit="open = false">
            @csrf
            <button type="submit" class="block py-2 text-gray-700 w-full text-left">
              <i class="bi bi-box-arrow-right me-2"></i>Log Out
            </button>
          </form>
        @else
          <button @click="$store.modals.showLogin = true; open = false" class="block py-2 text-gray-700 border-t border-[#8f5d54]/20 mt-2 pt-2" style="">Log In</button>
          <button @click="$store.modals.showSignup = true; open = false" class="block text-white bg-[#8f5d54] px-4 py-2 rounded mt-2" style="">Sign Up</button>
        @endauth
      </div>
    </div>
  </div>

  {{-- SIGN UP MODAL --}}
  <div
    x-show="$store.modals.showSignup"
    x-transition
    @click.self="$store.modals.showSignup = false"
    x-cloak
    class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-60 p-4"
  >
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 text-gray-800 relative">
      <button
        @click="$store.modals.showSignup = false"
        class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
        ✕
      </button>

      <!-- STEP 1 FORM -->
      <template x-if="!$store.modals.signupSuccess">
        <form 
          id="signupForm"
          class="space-y-5"
          @submit.prevent="
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = {
              first_name: $refs.first_name.value,
              last_name: $refs.last_name.value,
              email: $refs.email.value,
              username: $refs.username.value,
              password: $refs.password.value,
              password_confirmation: $refs.password_confirmation.value,
            };

            $el.querySelector('button[type=submit]').innerText = 'Sending...';
            $el.querySelector('button[type=submit]').disabled = true;

            fetch('/register-step-one', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
              },
              credentials: 'same-origin',
              body: JSON.stringify(formData)
            })
            .then(async res => {
              let data;
              try {
                data = await res.json();
              } catch {
                throw new Error('Invalid JSON');
              }

              if (res.ok) {
                if (data.message || data.success) {
                  $store.modals.signupSuccess = true;
                } else {
                  alert('Something went wrong.');
                }
              } else if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                alert(errorMessages);
              } else {
                alert(data.message || 'Request failed.');
              }
            })
            .catch(err => {
              console.error(err);
              alert('Error sending request.');
            })
            .finally(() => {
              const btn = $el.querySelector('button[type=submit]');
              btn.innerText = 'Sign Up';
              btn.disabled = false;
            });
          "
        >

          <h2 class="text-2xl font-semibold text-center text-blue-600 mb-2">Create Your Account</h2>
          <p class="text-sm text-center text-gray-500 mb-4">Fill in your details below to get started.</p>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
              <input x-ref="first_name" id="first_name" name="first_name" type="text" placeholder="John" autocomplete="given-name"
                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                required>
            </div>
            <div>
              <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
              <input x-ref="last_name" id="last_name" name="last_name" type="text" placeholder="Doe" autocomplete="family-name"
                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                required>
            </div>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input x-ref="email" id="email" name="email" type="email" placeholder="example@email.com" autocomplete="email"
              class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
              required>
          </div>

          <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input x-ref="username" id="username" name="username" type="text" placeholder="myusername" autocomplete="username"
              class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
              required>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
              <input x-ref="password" id="password" name="password" type="password" placeholder="********"
                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                required>
            </div>
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
              <input x-ref="password_confirmation" id="password_confirmation" name="password_confirmation" type="password" placeholder="********"
                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                required>
            </div>
          </div>

          <button type="submit"
            class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all shadow-md">
            Sign Up
          </button>

          <p class="text-center text-sm text-gray-500 mt-3">
            Already have an account? 
            <a href="#" @click.prevent="$store.modals.showSignup = false; $store.modals.showLogin = true" class="text-blue-600 hover:underline font-medium">Log in</a>
          </p>
        </form>
      </template>

      <!-- STEP 2 SUCCESS -->
      <template x-if="$store.modals.signupSuccess">
        <div class="text-center py-6">
          <h2 class="text-2xl font-semibold text-blue-600 mb-3">Verify Your Email</h2>
          <p class="text-gray-600 mb-6 text-sm leading-relaxed">
            We've sent a verification link to your email. Please check your inbox and click the link to activate your account.
          </p>
          <button
            @click="$store.modals.showSignup = false; $store.modals.signupSuccess = false;"
            class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition-all shadow-md">
            Got It
          </button>
        </div>
      </template>
    </div>
  </div>

  <!-- LOGIN MODAL -->
  <div
    x-show="$store.modals.showLogin"
    x-transition
    @click.self="$store.modals.showLogin = false"
    x-cloak
    class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-60 p-4"
  >
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 text-gray-800 relative">
      <button
        @click="$store.modals.showLogin = false"
        class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
        ✕
      </button>
      <h2 class="text-2xl font-semibold mb-4 text-center text-blue-600">Log In</h2>
      <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
        @csrf
        <div>
          <label for="login_username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
          <input type="text" id="login_username" name="username" placeholder="Enter your username" autocomplete="username"
            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            required>
        </div>
        <div>
          <label for="login_password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input type="password" id="login_password" name="password" placeholder="Enter your password"
            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            required>
        </div>
        <button type="submit"
          class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all shadow-md">
          Log In
        </button>
      </form>
      <p class="text-center text-sm text-gray-500 mt-3">
        Don’t have an account? 
        <a href="#" @click.prevent="$store.modals.showLogin = false; $store.modals.showSignup = true" class="text-blue-600 hover:underline font-medium">Sign up</a>
      </p>
    </div>
  </div>
</header>

