@php
  $footerSettings = array_merge(
    \App\Models\SystemSetting::defaults(),
    is_array($systemSettings ?? null) ? $systemSettings : []
  );
  $footerTitle = trim((string) $footerSettings['footer_title']);
  $footerDescription = trim((string) $footerSettings['footer_description']);
  $footerSocialHeading = trim((string) $footerSettings['footer_social_heading']);
  $footerEmail = trim((string) $footerSettings['footer_email']);
  $footerSocialLinks = collect([
    [
      'url' => trim((string) $footerSettings['footer_instagram_url']),
      'label' => 'Follow us on Instagram',
      'icon' => 'bi bi-instagram',
    ],
    [
      'url' => trim((string) $footerSettings['footer_facebook_url']),
      'label' => 'Follow us on Facebook',
      'icon' => 'bi bi-facebook',
    ],
    [
      'url' => trim((string) $footerSettings['footer_messenger_url']),
      'label' => 'Contact us on Messenger',
      'icon' => 'bi bi-messenger',
    ],
    [
      'url' => $footerEmail !== '' ? 'mailto:'.$footerEmail : '',
      'label' => 'Email us',
      'icon' => 'bi bi-envelope-fill',
    ],
  ])->filter(fn (array $link): bool => $link['url'] !== '')->values();
  $nav = config('navigation.main', []);
  $informationLinks = \App\Models\InformationPage::navigationItems();
  $policyLinks = \App\Models\PolicyPage::navigationItems();
@endphp

<footer class="bg-brand-dark text-white mt-0">
  @if ($footerSocialHeading !== '' || $footerSocialLinks->isNotEmpty())
    <!-- Social Media Banner -->
    <div class="bg-brand-light py-4">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col justify-center items-center">
          @if ($footerSocialHeading !== '')
            <div class="text-center{{ $footerSocialLinks->isNotEmpty() ? ' mb-3' : '' }}">
              <span class="text-lg font-semibold">{{ $footerSocialHeading }}</span>
            </div>
          @endif
          @if ($footerSocialLinks->isNotEmpty())
            <div class="flex space-x-4">
              @foreach ($footerSocialLinks as $socialLink)
                <a href="{{ $socialLink['url'] }}" class="text-white hover:text-gray-200 hover:scale-110 transition-all duration-300 p-2 rounded-full hover:bg-white/10" aria-label="{{ $socialLink['label'] }}">
                  <i class="{{ $socialLink['icon'] }} text-2xl"></i>
                </a>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
  @endif

  <!-- Main Footer Content -->
  <div class="py-8">
    <div class="max-w-7xl mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Company Info -->
        <div class="text-center md:text-left">
          <h3 class="text-xl font-bold uppercase mb-3">{{ $footerTitle }}</h3>
          <p class="text-gray-200 leading-relaxed text-base whitespace-pre-line">
            {{ $footerDescription }}
          </p>
        </div>

        <!-- Quick Links -->
        <div class="text-left">
          <h3 class="text-xl font-semibold uppercase mb-3 cursor-pointer md:cursor-default flex justify-between items-center" onclick="toggleSection(this)">
            <span>EXPLORE</span>
            <i class="bi bi-plus md:hidden"></i>
          </h3>
          <ul class="space-y-1 hidden md:block">
            @foreach ($nav as $item)
              <li>
                <a href="{{ url($item['path']) }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">
                  {{ $item['label'] }}
                </a>
              </li>
            @endforeach
          </ul>
        </div>

        <!-- Useful Links -->
        <div class="text-left">
          <h3 class="text-xl font-semibold uppercase mb-3 cursor-pointer md:cursor-default flex justify-between items-center" onclick="toggleSection(this)">
            <span>INFORMATION</span>
            <i class="bi bi-plus md:hidden"></i>
          </h3>
          <ul class="space-y-1 hidden md:block">
            @foreach($informationLinks as $informationLink)
              <li>
                <a href="{{ route($informationLink['route']) }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">
                  {{ $informationLink['label'] }}
                </a>
              </li>
            @endforeach
          </ul>
        </div>

        <!-- Contact Info -->
        <div class="text-left">
          <h3 class="text-xl font-semibold uppercase mb-3 cursor-pointer md:cursor-default flex justify-between items-center" onclick="toggleSection(this)">
            <span>CONTACT</span>
            <i class="bi bi-plus md:hidden"></i>
          </h3>
          <div class="space-y-2 hidden md:block">
            <div class="flex items-center justify-center md:justify-start">
              <i class="bi bi-geo-alt-fill mr-3 text-gray-200"></i>
              <span class="text-gray-200 text-base">Calaca, Batangas, Philippines</span>
            </div>
            @if ($footerEmail !== '')
              <div class="flex items-center justify-center md:justify-start">
                <i class="bi bi-envelope-fill mr-3 text-gray-200"></i>
                <a href="mailto:{{ $footerEmail }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">
                  {{ $footerEmail }}
                </a>
              </div>
            @endif
            <div class="flex items-center justify-center md:justify-start">
              <i class="bi bi-telephone-fill mr-3 text-gray-200"></i>
              <a href="tel:+639051294741" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">
                +63 956 319 2448
              </a>
            </div>
            <div class="flex items-center justify-center md:justify-start">
              <i class="bi bi-clock-fill mr-3 text-gray-200"></i>
              <span class="text-gray-200 text-base">Open Daily: 8:00 AM - 6:00 PM</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Policies Section -->
      <div class="mt-6 pt-6 border-t border-white/20">
        <div class="text-center">
          <h3 class="text-xl font-semibold uppercase mb-3">POLICIES & TERMS</h3>
          <div class="flex flex-wrap justify-center gap-6">
            @foreach($policyLinks as $policyLink)
              <a href="{{ route($policyLink['route']) }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">
                {{ $policyLink['label'] }}
              </a>
            @endforeach
          </div>
        </div>
      </div>

      <!-- Copyright -->
      <div class="mt-6 pt-4 border-t border-white/20">
        <p class="text-center text-gray-200 text-base" style="">
          © 2026 CALAIA FARM. ALL RIGHTS RESERVED.
        </p>
      </div>
    </div>
  </div>

  <script>
    function toggleSection(header) {
      const content = header.nextElementSibling;
      const icon = header.querySelector('i');
      content.classList.toggle('hidden');
      if (content.classList.contains('hidden')) {
        icon.classList.remove('bi-dash');
        icon.classList.add('bi-plus');
      } else {
        icon.classList.remove('bi-plus');
        icon.classList.add('bi-dash');
      }
    }
  </script>
</footer>
