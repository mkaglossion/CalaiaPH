<footer class="bg-[#8f5d54] text-white py-12 mt-12">
  <div class="max-w-7xl mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center md:text-left">
      <div class="md:pl-4">
        <h3 class="text-2xl font-semibold uppercase inline-block border-b-2 border-white/90 pb-2 mb-4">CALAIA.PH</h3>
        <p class="tracking-wide leading-6 mt-4 text-sm md:text-base">YOUR PERFECT GETAWAY<br>DESTINATION<br>FOR RELAXATION AND ADVENTURE.</p>
      </div>

      <div>
        <h3 class="text-xl font-semibold uppercase inline-block border-b-2 border-white/90 pb-2 mb-4">QUICK LINKS</h3>
        <ul class="mt-4 space-y-2 text-sm md:text-base tracking-wide">
          <li>HOME</li>
          <li><a href="{{ url('/daytour') }}" class="hover:underline">DAY TOUR</a></li>
          <li><a href="{{ url('/staycation') }}" class="hover:underline">WHERE TO STAY</a></li>
          <li><a href="{{ url('/activities') }}" class="hover:underline">WHAT TO DO</a></li>
          <li><a href="{{ url('/dining') }}" class="hover:underline">FOOD &amp; BEVERAGE</a></li>
          <li><a href="{{ url('/groups') }}" class="hover:underline">SPECIAL EVENTS</a></li>
          <li><a href="{{ url('/farm') }}" class="hover:underline">FARM</a></li>
          <li><a href="{{ url('/contact') }}" class="hover:underline">CONTACT US</a></li>
        </ul>
      </div>

      <div>
        <h3 class="text-xl font-semibold uppercase inline-block border-b-2 border-white/90 pb-2 mb-4">PROFILE</h3>
        <ul class="mt-4 space-y-2 text-sm md:text-base tracking-wide">
          <li><a href="{{ url('/our-story') }}" class="hover:underline">OUR STORY</a></li>
          <li><a href="{{ url('/sustainability') }}" class="hover:underline">SUSTAINABILITY</a></li>
          <li><a href="{{ url('/services') }}" class="hover:underline">OUR SERVICES</a></li>
        </ul>
      </div>

      <div>
        <h3 class="text-xl font-semibold uppercase inline-block border-b-2 border-white/90 pb-2 mb-4">PRIVACY &amp; TERMS</h3>
        <ul class="mt-4 space-y-2 text-sm md:text-base tracking-wide">
          <li><a href="{{ url('/privacy-notice') }}" class="hover:underline">PRIVACY NOTICE</a></li>
          <li><a href="{{ url('/calaia-policy') }}" class="hover:underline">CALAIA POLICY</a></li>
          <li><a href="{{ url('/cookies-policy') }}" class="hover:underline">COOKIES POLICY</a></li>
        </ul>
      </div>
    </div>

    <hr class="border-t border-white/20 my-8">

    <p class="text-center text-sm md:text-base tracking-wider">&copy; {{ date('Y') }} CALAIA FARM. ALL RIGHTS RESERVED.</p>
  </div>
</footer>