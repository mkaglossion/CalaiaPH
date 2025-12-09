<footer class="bg-[#8f5d54] text-white mt-0">
  <!-- Social Media Banner -->
  <div class="bg-[#7a4d44] py-4">
    <div class="max-w-7xl mx-auto px-6">
      <div class="flex flex-col justify-center items-center">
        <div class="text-center mb-3">
          <span class="text-lg font-semibold">Get connected with us on social networks:</span>
        </div>
        <div class="flex space-x-4">
          <!-- Instagram -->
          <a href="https://www.instagram.com/calaia.ph/" class="text-white hover:text-gray-200 hover:scale-110 transition-all duration-300 p-2 rounded-full hover:bg-white/10" aria-label="Follow us on Instagram">
            <i class="bi bi-instagram text-2xl"></i>
          </a>
          <!-- Facebook -->
          <a href="https://www.facebook.com/calaia.ph/" class="text-white hover:text-gray-200 hover:scale-110 transition-all duration-300 p-2 rounded-full hover:bg-white/10" aria-label="Follow us on Facebook">
            <i class="bi bi-facebook text-2xl"></i>
          </a>
          <!-- Messenger -->
          <a href="https://m.me/410774518796277" class="text-white hover:text-gray-200 hover:scale-110 transition-all duration-300 p-2 rounded-full hover:bg-white/10" aria-label="Contact us on Messenger">
            <i class="bi bi-messenger text-2xl"></i>
          </a>
          <!-- Email -->
          <a href="mailto:calaia.ph@gmail.com" class="text-white hover:text-gray-200 hover:scale-110 transition-all duration-300 p-2 rounded-full hover:bg-white/10" aria-label="Email us">
            <i class="bi bi-envelope-fill text-2xl"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Footer Content -->
  <div class="py-8">
    <div class="max-w-7xl mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Company Info -->
        <div class="text-center md:text-left">
          <h3 class="text-xl font-bold uppercase mb-3">CALAIA FARM</h3>
          <p class="text-gray-200 leading-relaxed text-base" style="">
            Your perfect getaway destination for relaxation and adventure.
            Experience nature at its finest in Calaca, Batangas.
          </p>
        </div>

        <!-- Quick Links -->
        <div class="text-left">
          <h3 class="text-xl font-semibold uppercase mb-3 cursor-pointer md:cursor-default flex justify-between items-center" onclick="toggleSection(this)">
            <span>EXPLORE</span>
            <i class="bi bi-plus md:hidden"></i>
          </h3>
          <ul class="space-y-1 hidden md:block">
            <li><a href="{{ url('/') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">Home</a></li>
            <li><a href="{{ url('/daytour') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">Day Tour</a></li>
            <li><a href="{{ url('/staycation') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">Where to Stay</a></li>
            <li><a href="{{ url('/activities') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">What to Do</a></li>
            <li><a href="{{ url('/dining') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">Food & Beverage</a></li>
            <li><a href="{{ url('/groups') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">Special Events</a></li>
            <li><a href="{{ url('/farm') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">Our Farm</a></li>
          </ul>
        </div>

        <!-- Useful Links -->
        <div class="text-left">
          <h3 class="text-xl font-semibold uppercase mb-3 cursor-pointer md:cursor-default flex justify-between items-center" onclick="toggleSection(this)">
            <span>INFORMATION</span>
            <i class="bi bi-plus md:hidden"></i>
          </h3>
          <ul class="space-y-1 hidden md:block">
            <li><a href="{{ url('/our-story') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">Our Story</a></li>
            <li><a href="{{ url('/sustainability') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">Sustainability</a></li>
            <li><a href="{{ url('/our-services') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">Our Services</a></li>
            <li><a href="{{ url('/contact') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">Contact Us</a></li>
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
            <div class="flex items-center justify-center md:justify-start">
              <i class="bi bi-envelope-fill mr-3 text-gray-200"></i>
              <a href="mailto:calaia.ph@gmail.com" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">
                calaia.ph@gmail.com
              </a>
            </div>
            <div class="flex items-center justify-center md:justify-start">
              <i class="bi bi-telephone-fill mr-3 text-gray-200"></i>
              <a href="tel:+639051294741" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">
                +63 905 129 4741
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
            <a href="{{ url('/privacy-notice') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">
              Privacy Notice
            </a>
            <a href="{{ url('/calaia-policy') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">
              Calaia Policy
            </a>
            <a href="{{ url('/cookies-policy') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">
              Cookies Policy
            </a>
            <a href="{{ url('/terms-service') }}" class="text-gray-200 hover:text-white hover:underline transition-colors duration-200 text-base">
              Terms of Service
            </a>
          </div>
        </div>
      </div>

      <!-- Copyright -->
      <div class="mt-6 pt-4 border-t border-white/20">
        <p class="text-center text-gray-200 text-base" style="">
          © 2025 CALAIA FARM. ALL RIGHTS RESERVED.
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
