{{-- SECTION: Visit or Contact Us --}}
<section id="contact" class="py-16 bg-linear-to-br from-primary/5 to-primary/10" aria-labelledby="contact-heading">
  <div class="policy-container px-4">
    <header class="text-center mb-12">
      <h2 id="contact-heading" class="font-bold text-[#8f5d54] mb-4" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
        Visit or Contact Us
      </h2>
      <p class="text-gray-600 max-w-2xl mx-auto leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
        We'd love to hear from you about visiting, volunteering, or partnership opportunities.
      </p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
      {{-- Contact Form --}}
      <x-contact-form action="{{ route('contact.submit') }}" />

      {{-- Contact Information --}}
      <div class="space-y-8">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
          <h3 class="font-semibold text-[#8f5d54] mb-6" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Visit Our Farm</h3>
          <ul class="space-y-4" role="list">
            <li class="flex items-start">
              <i class="fa fa-location-dot text-[#8f5d54] mt-1 mr-4 shrink-0" aria-hidden="true"></i>
              <div>
                <span class="font-medium text-gray-900">Address</span>
                <address class="text-gray-600 not-italic">Purok 3, Brgy. Buso-buso, Calaca, Philippines</address>
              </div>
            </li>
            <li class="flex items-center">
              <i class="fa fa-phone text-[#8f5d54] mr-4 shrink-0" aria-hidden="true"></i>
              <div>
                <span class="font-medium text-gray-900">Phone</span>
                <a href="tel:+639051294741" class="text-gray-600 hover:text-[#8f5d54] transition-colors">+63 905 129 4741</a>
              </div>
            </li>
            <li class="flex items-center">
              <i class="fa fa-envelope text-[#8f5d54] mr-4 shrink-0" aria-hidden="true"></i>
              <div>
                <span class="font-medium text-gray-900">Email</span>
                <a href="mailto:calaia@calaia.com" class="text-gray-600 hover:text-[#8f5d54] transition-colors">calaia@calaia.com</a>
              </div>
            </li>
            <li class="flex items-start">
              <i class="fa fa-clock text-[#8f5d54] mt-1 mr-4 shrink-0" aria-hidden="true"></i>
              <div>
                <span class="font-medium text-gray-900">Hours</span>
                <span class="text-gray-600 block">Monday–Saturday: 9AM – 3PM</span>
              </div>
            </li>
            <li class="flex items-center gap-4 pt-2">
              <span class="font-medium text-gray-900">Follow us</span>
              <div class="flex gap-3">
                <a href="#" aria-label="Follow us on Instagram" class="text-gray-500 hover:text-[#8f5d54] transition-colors">
                  <i class="fab fa-instagram text-xl" aria-hidden="true"></i>
                </a>
                <a href="#" aria-label="Follow us on Facebook" class="text-gray-500 hover:text-[#8f5d54] transition-colors">
                  <i class="fab fa-facebook text-xl" aria-hidden="true"></i>
                </a>
                <a href="#" aria-label="Contact us on Messenger" class="text-gray-500 hover:text-[#8f5d54] transition-colors">
                  <i class="fab fa-facebook-messenger text-xl" aria-hidden="true"></i>
                </a>
              </div>
            </li>
          </ul>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
          <h3 class="font-semibold text-[#8f5d54] mb-6" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Location & Directions</h3>
          <div class="rounded-xl overflow-hidden mb-6 shadow-md">
            <iframe
              src="https://www.google.com/maps?q=Calaca%20Nature%20Park&output=embed"
              class="w-full h-64 border-0"
              loading="lazy"
              title="Calaia Farm Location Map"
            ></iframe>
          </div>
          <h4 class="text-lg font-semibold text-[#8f5d54] mb-3">Getting Here</h4>
          <p class="text-gray-600 mb-4 leading-relaxed" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
            Calaia Nature Park is located in Calaca, Batangas along the main road near Sitio Calas from the town proper of Calaca.
          </p>
          <a href="https://maps.google.com" target="_blank" rel="noopener"
             class="inline-flex items-center text-[#8f5d54] hover:text-[#8f5d54]-dark font-medium transition-colors">
            Get Directions with Google Maps <i class="fas fa-chevron-right text-xs ml-2" aria-hidden="true"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>