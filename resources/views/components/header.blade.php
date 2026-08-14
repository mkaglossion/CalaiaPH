@props(['active' => 'home'])

@php
  $cart = session('cart', []);
  $cartCount = 0;
  $logoSource = $systemSettings['logo_url'] ?? null;
  $desktopLogo = $logoSource
    ? \App\Helpers\CloudinaryHelper::optimize($logoSource, 420, false, false, 'good')
    : null;
  $mobileLogo = $desktopLogo;

  if (is_array($cart)) {
    foreach ($cart as $item) {
      $cartCount += $item['quantity'] ?? 0;
    }
  }

  $nav = config('navigation.main', []);
@endphp

<header
  x-data="{
    mobileOpen: false,
    init() {
      // Initialize modal store with default closed state
      try {
        if (!Alpine.store('modals')) {
          Alpine.store('modals', {
            showLogin: false,
            showSignup: false,
            signupSuccess: false,
            showCart: false,
            showForgotPassword: false
          });
        } else {
          // Ensure modals are closed on page load
          Alpine.store('modals').showLogin = false;
          Alpine.store('modals').showSignup = false;
          Alpine.store('modals').showCart = false;
          Alpine.store('modals').showForgotPassword = false;
        }
      } catch (e) {
        console.error('Alpine store initialization error:', e);
        // Fallback: create store manually
        Alpine.store('modals', {
          showLogin: false,
          showSignup: false,
          signupSuccess: false,
          showCart: false,
          showForgotPassword: false
        });
      }
    },
    closeAll() {
      this.mobileOpen = false;
      const m = Alpine.store('modals');
      if (m) { 
        m.showLogin = false; 
        m.showSignup = false; 
        m.showForgotPassword = false; 
      }
    }
  }"
  x-init="init()"
  x-effect="
    const m = Alpine.store('modals');
    const lock = mobileOpen || (m && (m.showLogin || m.showSignup || m.showForgotPassword || m.showCart));
    if (lock) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  "
  @keydown.escape.window="closeAll()"
  class="bg-brand text-white shadow-lg sticky top-0 z-50"
>

  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

    <!-- ✅ DESKTOP LOGO ROW (ABOVE NAV) -->
    <div class="hidden md:flex justify-center py-3">
      <a href="{{ url('/') }}" class="inline-flex items-center justify-center">
        @if($desktopLogo)
          <img
            src="{{ $desktopLogo }}"
            alt="{{ $systemSettings['app_name'] ?? 'Calaia PH' }}"
            class="h-20 w-auto object-contain"
          >
        @else
          <span class="text-xl font-semibold tracking-[0.35em] text-white uppercase">
            {{ $systemSettings['app_name'] ?? 'Calaia PH' }}
          </span>
        @endif
      </a>
    </div>

    <!-- ✅ NAV + ACTIONS ROW (DESKTOP) -->
    <div class="hidden md:flex items-center justify-between pb-4 gap-2">

      <!-- left spacer (for balance) -->
      <div class="w-16"></div>

<!-- NAV centered with shrink -->
       <nav class="flex justify-center gap-4 shrink-0 sticky top-0">
        @foreach ($nav as $item)
          <a
            href="{{ url($item['path']) }}"
            @class([
              'relative uppercase font-medium text-base tracking-wide transition',
              'text-white hover:text-white/80',
            ])
            aria-current="{{ $active === $item['key'] ? 'page' : 'false' }}"
          >
            <span @class([
              'inline-block py-1 whitespace-nowrap',
              // underline without layout shift
              'after:absolute after:left-0 after:-bottom-1 after:h-[1px] after:w-full after:bg-white after:content-[\'\'] after:transition after:opacity-0',
              $active === $item['key'] ? 'border-b-2 border-white' : 'hover:after:opacity-100'
            ])>
              {{ $item['label'] }}
            </span>
          </a>
        @endforeach
      </nav>

      <!-- Actions right -->
      <div class="flex items-center justify-end gap-3 shrink-0">
        @auth
          <!-- User dropdown -->
          <div class="relative" x-data="{ userOpen: false }">
            <button
              type="button"
              @click="userOpen = !userOpen"
              class="inline-flex items-center justify-center rounded-lg p-2 hover:bg-white/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40"
              :aria-expanded="userOpen.toString()"
            >
              <span class="sr-only">Open user menu</span>
              <svg xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor"
                class="w-6 h-6 text-white">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 1115 0v.75H4.5v-.75z" />
              </svg>
            </button>

            <div
              x-cloak
              x-show="userOpen"
              @click.away="userOpen = false"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 scale-95"
              x-transition:enter-end="opacity-100 scale-100"
              x-transition:leave="transition ease-in duration-150"
              x-transition:leave-start="opacity-100 scale-100"
              x-transition:leave-end="opacity-0 scale-95"
              style="display: none;"
              class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden z-50"
            >
              <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                <i class="bi bi-person me-2"></i>My Profile
              </a>
              <a href="{{ route('booking.calendar') }}?type=staycation" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                <i class="bi bi-calendar-check me-2"></i>Book Staycation
              </a>
              <a href="{{ route('booking.calendar') }}?type=daytour" x-show="false" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                <i class="bi bi-sun me-2"></i>Book Day Tour
              </a>
              <a href="{{ route('profile.show') }}#bookings" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                <i class="bi bi-calendar-event me-2"></i>My Bookings
              </a>

              @if (Auth::user()->user_type === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                  <i class="bi bi-gear me-2"></i>Admin Panel
                </a>
              @endif

              <a href="{{ route('checkinout') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition">
                <i class="bi bi-person-check me-2"></i>Check-in / Check-out
              </a>

              <div class="border-t border-gray-200 my-2"></div>

              <a href="{{ route('logout') }}" class="block w-full text-left px-4 py-2 hover:bg-red-50 transition text-red-600 font-medium">
                <i class="bi bi-box-arrow-right me-2"></i>Log Out
              </a>
            </div>
          </div>

          <x-cart />
        @else
          <div class="flex items-center gap-2">
            <button
              @click="$store.modals.showLogin = true"
              class="text-white hover:text-white/80 font-medium text-base transition px-4 py-2 whitespace-nowrap"
            >
              Log In
            </button>
            <button
              @click="$store.modals.showSignup = true"
              class="bg-white text-brand px-4 py-2 rounded-lg font-semibold text-base hover:bg-gray-100 transition shadow-md whitespace-nowrap"
            >
              Sign Up
            </button>
          </div>

          <x-cart />
        @endauth
      </div>
    </div>

    <!-- ✅ MOBILE TOP BAR -->
    <div class="md:hidden flex items-center justify-between py-3">
      <!-- Logo -->
      <a href="{{ url('/') }}" class="inline-flex items-center">
        @if($mobileLogo)
          <img
            src="{{ $mobileLogo }}"
            alt="{{ $systemSettings['app_name'] ?? 'Calaia PH' }}"
            class="h-16 w-auto object-contain"
          >
        @else
          <span class="text-base font-semibold tracking-[0.25em] text-white uppercase">
            {{ $systemSettings['app_name'] ?? 'Calaia PH' }}
          </span>
        @endif
      </a>

      <!-- Actions + Hamburger -->
      <div class="flex items-center gap-2">
        <x-cart />

        <button
          type="button"
          class="inline-flex items-center justify-center rounded-lg p-2 hover:bg-white/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40"
          @click="mobileOpen = !mobileOpen"
          aria-controls="mobile-menu"
          :aria-expanded="mobileOpen.toString()"
        >
          <span class="sr-only">Open menu</span>
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- ✅ MOBILE OFF-CANVAS MENU -->
  <div x-cloak x-show="mobileOpen" style="display: none;" class="md:hidden">
    <div class="fixed inset-0 z-40 bg-black/40" @click="mobileOpen = false"></div>

    <div
      id="mobile-menu"
      class="fixed inset-0 z-50 w-full overflow-y-auto bg-brand shadow-2xl"
      style="height: 100dvh;"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="-translate-x-4 opacity-0"
      x-transition:enter-end="translate-x-0 opacity-100"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="translate-x-0 opacity-100"
      x-transition:leave-end="-translate-x-4 opacity-0"
    >
        <div class="flex items-center justify-between px-4 py-4 border-b border-brand/15">
        <span class="font-semibold tracking-wide text-white">Menu</span>
        <button
          type="button"
          class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-white transition hover:bg-white/20"
          @click="mobileOpen = false"
          aria-label="Close menu"
        >
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="px-4 py-3 space-y-1">
        @foreach ($nav as $item)
          <a
            href="{{ url($item['path']) }}"
            @click.prevent="mobileOpen = false; window.location.href = '{{ url($item['path']) }}'"
            @class([
              'block rounded-lg px-3 py-2 uppercase tracking-wide transition text-white',
              $active === $item['key'] ? 'bg-white/70 font-semibold' : 'hover:bg-white/50'
            ])
          >
            {{ $item['label'] }}
          </a>
        @endforeach

        <a
          href="{{ route('cart.index') }}"
          data-js-cart-button
          @click.prevent="mobileOpen = false; window.location.href = '{{ route('cart.index') }}'"
          class="mt-2 flex items-center gap-2 rounded-lg px-3 py-2 uppercase tracking-wide text-white transition hover:bg-white/50"
        >
          <span>Cart</span>
          @if($cartCount > 0)
            <span data-js-cart-count class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-white px-1.5 text-xs font-semibold text-brand shadow-sm">
              {{ $cartCount }}
            </span>
          @endif
        </a>

          <div class="pt-3 mt-3 border-t border-brand/15">
          @auth
            <a href="{{ route('profile.show') }}" @click.prevent="mobileOpen = false; window.location.href = '{{ route('profile.show') }}'" class="block px-3 py-2 rounded-lg hover:bg-white/50 transition text-white">
              <i class="bi bi-person me-2"></i>Profile
            </a>
            <a href="{{ route('booking.calendar') }}?type=staycation" @click.prevent="mobileOpen = false; window.location.href = '{{ route('booking.calendar') }}?type=staycation'" class="block px-3 py-2 rounded-lg hover:bg-white/50 transition text-white">
              <i class="bi bi-calendar-check me-2"></i>Book Staycation
            </a>
            <a href="{{ route('booking.calendar') }}?type=daytour" x-show="false" @click.prevent="mobileOpen = false; window.location.href = '{{ route('booking.calendar') }}?type=daytour'" class="block px-3 py-2 rounded-lg hover:bg-white/50 transition text-white">
              <i class="bi bi-sun me-2"></i>Book Day Tour
            </a>
            <a href="{{ route('profile.show') }}#bookings" @click.prevent="mobileOpen = false; window.location.href = '{{ route('profile.show') }}#bookings'" class="block px-3 py-2 rounded-lg hover:bg-white/50 transition text-white">
              <i class="bi bi-calendar-event me-2"></i>My Bookings
            </a>

            @if (Auth::user()->user_type === 'admin')
              <a href="{{ route('admin.dashboard') }}" @click.prevent="mobileOpen = false; window.location.href = '{{ route('admin.dashboard') }}'" class="block px-3 py-2 rounded-lg hover:bg-white/50 transition text-white">
                <i class="bi bi-gear me-2"></i>Admin Panel
              </a>
            @endif

            <a href="{{ route('checkinout') }}" @click.prevent="mobileOpen = false; window.location.href = '{{ route('checkinout') }}'" class="block px-3 py-2 rounded-lg hover:bg-white/50 transition text-white">
              <i class="bi bi-person-check me-2"></i>Check-in / Check-out
            </a>

            <a href="{{ route('logout') }}" @click.prevent="mobileOpen = false; window.location.href = '{{ route('logout') }}'" class="block px-3 py-3 rounded-lg hover:bg-white/50 transition text-white pb-12">
              <i class="bi bi-box-arrow-right me-2"></i>Log Out
            </a>
          @else
            <button
              @click="$store.modals.showLogin = true; mobileOpen = false"
              class="w-full text-left px-3 py-2 rounded-lg hover:bg-white/50 transition text-white"
            >
              Log In
            </button>
            <button
              @click="$store.modals.showSignup = true; mobileOpen = false"
              class="w-full mt-2 bg-white text-brand px-4 py-2.5 rounded-lg font-semibold hover:bg-gray-100 transition shadow"
            >
              Sign Up
            </button>
          @endauth
        </div>
      </div>
    </div>
  </div>

  {{-- SIGN UP MODAL --}}
  <div
    x-show="$store.modals.showSignup"
    x-transition
    @click.self="$store.modals.showSignup = false"
    x-cloak
    style="display: none;"
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
                required autocomplete="new-password">
            </div>
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
              <input x-ref="password_confirmation" id="password_confirmation" name="password_confirmation" type="password" placeholder="********"
                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                required autocomplete="new-password">
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
    style="display: none;"
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
          <label for="login_username" class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
          <input type="text" id="login_username" name="username" placeholder="Enter your username or email" autocomplete="username"
            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            required>
        </div>
        <div>
          <label for="login_password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input type="password" id="login_password" name="password" placeholder="Enter your password"
            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            required autocomplete="current-password">
        </div>
        <button type="submit"
          class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all shadow-md">
          Log In
        </button>
      </form>

      <div class="mt-6">
        <div class="flex items-center gap-3" aria-hidden="true">
          <div class="h-px flex-1 bg-gray-200"></div>
          <span class="inline-flex items-center rounded-full border border-gray-200 px-3 py-1 text-xs font-semibold tracking-[0.3em] text-gray-500">
            OR
          </span>
          <div class="h-px flex-1 bg-gray-200"></div>
        </div>

        <div class="mt-4 space-y-3">
          <a
            href="{{ route('auth.google.redirect', ['intended' => request()->fullUrl()]) }}"
            aria-label="Continue with Google"
            class="flex w-full items-center justify-center gap-3 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
              <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.3-.8 2.4-1.8 3.2l3 2.3c1.8-1.7 2.8-4.1 2.8-6.9 0-.7-.1-1.4-.2-2H12Z"/>
              <path fill="#34A853" d="M12 22c2.6 0 4.8-.9 6.5-2.5l-3-2.3c-.8.6-1.9 1-3.5 1-2.7 0-5-1.8-5.8-4.3l-3.1 2.4C4.9 19.5 8.2 22 12 22Z"/>
              <path fill="#4A90E2" d="M6.2 13.9c-.2-.6-.3-1.3-.3-1.9s.1-1.3.3-1.9l-3.1-2.4C2.4 9.1 2 10.5 2 12s.4 2.9 1.1 4.3l3.1-2.4Z"/>
              <path fill="#FBBC05" d="M12 5.8c1.5 0 2.8.5 3.8 1.5l2.8-2.8C16.8 2.9 14.6 2 12 2 8.2 2 4.9 4.5 3.1 7.7l3.1 2.4C7 7.6 9.3 5.8 12 5.8Z"/>
            </svg>
            <span>Continue with Google</span>
          </a>

          <a
            href="{{ route('auth.facebook.redirect', ['intended' => request()->fullUrl()]) }}"
            aria-label="Continue with Facebook"
            class="flex w-full items-center justify-center gap-3 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <svg class="h-5 w-5 shrink-0 text-[#1877F2]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M13.5 22v-8.2h2.8l.4-3.2h-3.2V8.6c0-.9.3-1.6 1.6-1.6h1.8V4.1c-.3 0-1.4-.1-2.6-.1-2.6 0-4.3 1.6-4.3 4.5v2.1H8v3.2h2.6V22h2.9Z"/>
            </svg>
            <span>Continue with Facebook</span>
          </a>
        </div>
      </div>

      <div class="text-center mt-4">
        <a href="#"
          @click.prevent="
            $store.modals.showLogin = false;
            $nextTick(() => { $store.modals.showForgotPassword = true; })
          "
          class="text-sm text-blue-600 hover:text-blue-800 hover:underline"
        >
          Forgot your password?
        </a>
      </div>
      <p class="text-center text-sm text-gray-500 mt-3">
        Don't have an account?
        <a href="#" @click.prevent="$store.modals.showLogin = false; $store.modals.showSignup = true" class="text-blue-600 hover:underline font-medium">Sign up</a>
      </p>
    </div>
  </div>

  <!-- FORGOT PASSWORD MODAL -->
  <div
    x-show="$store.modals.showForgotPassword"
    x-transition
    @click.self="$store.modals.showForgotPassword = false"
    x-cloak
    style="display: none;"
    class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-60 p-4"
  >
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 text-gray-800 relative">
      <button
        @click="$store.modals.showForgotPassword = false; $nextTick(() => { $store.modals.showLogin = true; })"
        class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
        ✕
      </button>

      <h2 class="text-2xl font-semibold mb-4 text-center text-blue-600">Forgot Password</h2>

      <form
        id="forgotPasswordForm"
        class="space-y-4"
        @submit.prevent="
          const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
          const formData = { email: $refs.forgotEmail.value };

          $el.querySelector('button[type=submit]').innerText = 'Sending...';
          $el.querySelector('button[type=submit]').disabled = true;

          fetch('{{ route('forgot.password.post') }}', {
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
            try { data = await res.json(); } catch { throw new Error('Invalid JSON'); }

            if (res.ok) {
              if (data.success) {
                alert(data.message);
                $store.modals.showForgotPassword = false;
                $nextTick(() => { $store.modals.showLogin = true; });
              } else {
                alert('Something went wrong.');
              }
            } else if (data.errors) {
              const errorMessages = Object.values(data.errors).flat().join('\\n');
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
            btn.innerText = 'Send Reset Link';
            btn.disabled = false;
          });
        "
      >
        <div>
          <label for="forgotEmail" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
          <input x-ref="forgotEmail" id="forgotEmail" name="email" type="email" placeholder="Enter your email address"
            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            required autocomplete="email">
        </div>

        <button type="submit"
          class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all shadow-md">
          Send Reset Link
        </button>
      </form>

      <div class="text-center mt-4">
        <a href="#"
          @click.prevent="
            $store.modals.showForgotPassword = false;
            $nextTick(() => { $store.modals.showLogin = true; })
          "
          class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
          Back to login
        </a>
      </div>
    </div>
  </div>
</header>
