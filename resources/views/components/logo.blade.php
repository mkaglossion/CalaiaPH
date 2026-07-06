<div x-data="{}">
  @php
    $logoSource = $systemSettings['logo_url'] ?? null;
    $logoUrl = $logoSource
      ? \App\Helpers\CloudinaryHelper::optimize($logoSource, 360, false, false, 'good')
      : null;
  @endphp
  <a href="{{ url('/') }}" x-show="!$store.modals.showLogin && !$store.modals.showSignup" class="fixed -top-2 -left-2 md:-top-4 md:-left-4 z-40 md:z-50 hidden md:block">
    <div class="bg-[#f6f0eb] w-24 h-24 md:w-56 md:h-56 rounded-full flex items-center justify-center shadow-md overflow-hidden">
      @if($logoUrl)
        <img
          src="{{ $logoUrl }}"
          alt="{{ $systemSettings['app_name'] ?? 'Calaia PH' }}"
          class="w-[65%] h-[65%] object-contain"
        >
      @else
        <span class="px-4 text-center text-xs font-semibold tracking-[0.3em] text-[#8f5d54] uppercase">
          {{ $systemSettings['app_name'] ?? 'Calaia PH' }}
        </span>
      @endif
    </div>
  </a>
</div>
