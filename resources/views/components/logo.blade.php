<div x-data="{}">
  <a href="{{ url('/') }}" x-show="!$store.modals.showLogin && !$store.modals.showSignup" class="fixed -top-2 -left-2 md:-top-4 md:-left-4 z-40 md:z-50 hidden md:block">
    <div class="bg-[#f6f0eb] w-24 h-24 md:w-56 md:h-56 rounded-full flex items-center justify-center shadow-md overflow-hidden">
      <img
        src="{{ asset('assets/logo/CALAIA LOGO RUST.png') }}"
        alt="CALAIA LOGO RUST"
        class="w-[65%] h-[65%] object-contain"
      >
    </div>
  </a>
</div>
