<x-page-layout
    title="Calaia Farm - Terms & Conditions"
    description="Read our terms and conditions for visiting Calaia Farm. Learn about booking policies, visitor guidelines, and our commitment to your safety and enjoyment."
    keywords="Calaia Farm, terms and conditions, booking policies, visitor guidelines, farm policies, Philippines farm"
    active-header="calaia-policy"
>
    <!-- Hero Section -->
    <section class="relative h-[60vh] bg-cover bg-center flex items-center justify-center text-white overflow-hidden"
             style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset('images/facilities/terms-hero.jpg') }}')">
        <div class="absolute inset-0 bg-linear-to-br from-amber-900/40 to-amber-700/40"></div>
        <div class="relative z-10 text-center px-4 animate-fade-in">
            <h1 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">Terms & Conditions</h1>
            <p class="text-lg md:text-xl max-w-2xl mx-auto drop-shadow-md">
                Guidelines for your visit to Calaia Farm. Please read carefully before booking.
            </p>
        </div>
    </section>

    <!-- Terms & Conditions Section -->
    <div class="policy-subnav relative z-20 -mt-12">
        <div class="policy-container">
                <div class="flex justify-center gap-3">
                <a href="{{ url('/privacy-notice') }}" class="policy-pill secondary" @if(request()->is('privacy-notice')) aria-current="page" @endif>Privacy Notice</a>
                <a href="{{ url('/calaia-policy') }}" class="policy-pill" @if(request()->is('calaia-policy')) aria-current="page" @endif>Calaia Policy</a>
                <a href="{{ url('/cookies-policy') }}" class="policy-pill secondary" @if(request()->is('cookies-policy')) aria-current="page" @endif>Cookies Policy</a>
                <a href="{{ url('/terms-service') }}" class="policy-pill secondary" @if(request()->is('terms-service')) aria-current="page" @endif>Terms of Service</a>
            </div>
        </div>
    </div>
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="max-w-5xl mx-auto">

                <h1 class="text-4xl md:text-6xl font-bold text-center mb-12 text-gray-800 border-b-4 border-amber-800 pb-6">Calaia Farm Terms & Conditions</h1>

                <p class="text-center text-gray-600 mb-8"><strong>Last Updated:</strong> January 5, 2026</p>

                <!-- Welcome Section -->
                <div class="bg-amber-50 p-6 rounded-xl mb-8 border-l-4 border-amber-800">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-handshake text-amber-600"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-amber-800">Welcome to Calaia Farm</h2>
                    </div>
                    <p class="leading-relaxed">These terms and conditions outline the rules and regulations for the use of Calaia Farm's website and services. By accessing this website and using our services, you accept these terms and conditions in full.</p>
                </div>

                <!-- Acceptance of Terms -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-check-circle text-blue-600"></i>
                        </div>
                        Acceptance of Terms
                    </h2>
                    <p class="text-gray-600 leading-relaxed">By accessing and using Calaia Farm's facilities, website, and services, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, you must not use our services or visit our farm.</p>
                </div>

                <!-- Farm Visit Policies -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-farm text-green-600"></i>
                        </div>
                        Farm Visit Policies
                    </h2>

                    <!-- Booking and Reservations -->
                    <div class="bg-amber-50 border-2 border-amber-800 rounded-lg p-6 mb-6">
                        <h3 class="text-xl font-semibold text-amber-800 mb-4 flex items-center">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Booking and Reservations
                        </h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-check text-green-500 mt-1"></i>
                                <span class="text-sm text-gray-700">All bookings must be made in advance through our website or authorized channels</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-check text-green-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Minimum 50% deposit required to confirm reservation</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-check text-green-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Full payment due 48 hours before scheduled visit</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-check text-green-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Cancellations 72+ hours in advance: 100% refund</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-times text-red-500 mt-1"></i>
                                <span class="text-sm text-gray-700">No-shows forfeit their deposit</span>
                            </div>
                        </div>
                    </div>

                    <!-- Visitor Guidelines -->
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-800 mb-3 flex items-center">
                                <i class="fas fa-users mr-2"></i>
                                Visitor Guidelines
                            </h4>
                            <ul class="text-sm text-gray-700 space-y-2">
                                <li>• Check-in at reception required</li>
                                <li>• Valid ID required for adults</li>
                                <li>• Children under 12 must be accompanied</li>
                                <li>• Proper attire and closed-toe shoes recommended</li>
                            </ul>
                        </div>

                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-red-800 mb-3 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Prohibited Activities
                            </h4>
                            <ul class="text-sm text-gray-700 space-y-2">
                                <li>• No smoking in designated areas</li>
                                <li>• Alcohol only in designated zones</li>
                                <li>• No illegal drug use or possession</li>
                                <li>• No excessive alcohol consumption</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Health and Safety -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-green-800 mb-3 flex items-center">
                            <i class="fas fa-heartbeat mr-2"></i>
                            Health and Safety
                        </h4>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-plus-circle text-green-500 mt-1"></i>
                                <span class="text-sm text-gray-700">First aid services available on-site</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-user-md text-green-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Emergency medical services available</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-exclamation-circle text-amber-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Activities at your own risk</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-shield-alt text-blue-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Report allergies upon arrival</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accommodation Policies -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-bed text-purple-600"></i>
                        </div>
                        Accommodation Policies
                    </h2>

                    <!-- Check-in/out Times -->
                    <div class="bg-purple-50 border-2 border-purple-800 rounded-lg p-6 mb-6">
                        <h3 class="text-xl font-semibold text-purple-800 mb-4 flex items-center">
                            <i class="fas fa-clock mr-2"></i>
                            Check-in and Check-out Times
                        </h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-sign-in-alt text-green-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">Check-in</p>
                                    <p class="text-sm text-gray-600">2:00 PM</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-sign-out-alt text-red-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">Check-out</p>
                                    <p class="text-sm text-gray-600">12:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Accommodation Rules -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-home mr-2"></i>
                            Accommodation Rules
                        </h4>
                        <div class="grid md:grid-cols-2 gap-4">
                            <ul class="text-sm text-gray-700 space-y-2">
                                <li>• Maximum occupancy limits must be observed</li>
                                <li>• No outside visitors after 10:00 PM</li>
                                <li>• Quiet hours: 10:00 PM to 7:00 AM</li>
                            </ul>
                            <ul class="text-sm text-gray-700 space-y-2">
                                <li>• Guests responsible for property damages</li>
                                <li>• Daily housekeeping services provided</li>
                                <li>• Lost items are not our responsibility</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Payment Terms -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-credit-card text-indigo-600"></i>
                        </div>
                        Payment Terms
                    </h2>

                    <!-- Payment Methods -->
                    <div class="bg-indigo-50 border-2 border-indigo-800 rounded-lg p-6 mb-6">
                        <h3 class="text-xl font-semibold text-indigo-800 mb-4 flex items-center">
                            <i class="fas fa-money-bill-wave mr-2"></i>
                            Payment Methods & Pricing
                        </h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-check text-green-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Cash, credit cards, and bank transfers accepted</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-check text-green-500 mt-1"></i>
                                <span class="text-sm text-gray-700">All prices in Philippine Pesos (PHP)</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Prices subject to change without notice</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-plus-circle text-amber-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Additional charges for extra services</span>
                            </div>
                        </div>
                    </div>

                    <!-- Refund Policy -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-undo-alt mr-2"></i>
                            Refund Policy
                        </h4>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-3 bg-white rounded border">
                                <span class="text-sm font-medium">72+ hours notice</span>
                                <span class="text-sm text-green-600 font-semibold">100% refund</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-white rounded border">
                                <span class="text-sm font-medium">48-72 hours notice</span>
                                <span class="text-sm text-amber-600 font-semibold">50% refund</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-white rounded border">
                                <span class="text-sm font-medium">Less than 48 hours</span>
                                <span class="text-sm text-red-600 font-semibold">No refund</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-white rounded border">
                                <span class="text-sm font-medium">Weather-related cancellations</span>
                                <span class="text-sm text-blue-600 font-semibold">Full credit for future visits</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity and Event Policies -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-running text-teal-600"></i>
                        </div>
                        Activity & Event Policies
                    </h2>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Farm Activities -->
                        <div class="bg-teal-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-teal-800 mb-3 flex items-center">
                                <i class="fas fa-tractor mr-2"></i>
                                Farm Activities
                            </h4>
                            <ul class="text-sm text-gray-700 space-y-2">
                                <li>• Subject to weather conditions</li>
                                <li>• Age and health restrictions apply</li>
                                <li>• Follow staff safety instructions</li>
                                <li>• Protective gear provided when needed</li>
                            </ul>
                        </div>

                        <!-- Special Events -->
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-orange-800 mb-3 flex items-center">
                                <i class="fas fa-birthday-cake mr-2"></i>
                                Special Events
                            </h4>
                            <ul class="text-sm text-gray-700 space-y-2">
                                <li>• Signed contract and deposit required</li>
                                <li>• Final count confirmed 48 hours prior</li>
                                <li>• External caterers must be approved</li>
                                <li>• Comply with local noise regulations</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Environmental Policies -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-leaf text-green-600"></i>
                        </div>
                        Environmental & Sustainability
                    </h2>

                    <!-- Our Commitment -->
                    <div class="bg-green-50 border-2 border-green-800 rounded-lg p-6 mb-6">
                        <h3 class="text-xl font-semibold text-green-800 mb-4 flex items-center">
                            <i class="fas fa-seedling mr-2"></i>
                            Our Commitment to Sustainability
                        </h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Organic farming methods practiced</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Water conservation prioritized</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Waste minimized through composting</span>
                            </div>
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                <span class="text-sm text-gray-700">Local and seasonal products preferred</span>
                            </div>
                        </div>
                    </div>

                    <!-- Visitor Responsibilities -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-hands-helping mr-2"></i>
                            Visitor Responsibilities
                        </h4>
                        <div class="grid md:grid-cols-2 gap-4">
                            <ul class="text-sm text-gray-700 space-y-2">
                                <li>• Respect the natural environment</li>
                                <li>• Follow "Leave No Trace" principles</li>
                                <li>• Use designated paths and trails</li>
                            </ul>
                            <ul class="text-sm text-gray-700 space-y-2">
                                <li>• Do not disturb wildlife or animals</li>
                                <li>• Properly dispose of all waste</li>
                                <li>• Help preserve our farm's beauty</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Liability and Insurance -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-shield-alt text-red-600"></i>
                        </div>
                        Liability & Insurance
                    </h2>

                    <div class="bg-red-50 p-4 rounded-lg mb-6">
                        <p class="text-gray-700 leading-relaxed mb-4">Calaia Farm maintains comprehensive insurance coverage for our operations. However, visitors participate in all activities at their own risk. We recommend that visitors have their own travel and health insurance.</p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Limitations of Liability
                        </h4>
                        <ul class="text-sm text-gray-700 space-y-2">
                            <li>• Not liable for personal injury or property damage</li>
                            <li>• Not responsible for losses due to natural disasters</li>
                            <li>• Liability limited to amount paid for services</li>
                            <li>• Recommend personal travel/health insurance</li>
                        </ul>
                    </div>
                </div>

                <!-- Additional Policies -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-info-circle text-gray-600"></i>
                        </div>
                        Additional Policies
                    </h2>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Privacy Policy -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-800 mb-3 flex items-center">
                                <i class="fas fa-lock mr-2"></i>
                                Privacy and Data Protection
                            </h4>
                            <p class="text-sm text-gray-700">Your privacy is important to us. Please refer to our separate <a href="{{ route('privacy-notice') }}" class="text-amber-800 hover:text-amber-700 font-medium">Privacy Policy</a> for detailed information about how we collect, use, and protect your personal information.</p>
                        </div>

                        <!-- Photography -->
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-purple-800 mb-3 flex items-center">
                                <i class="fas fa-camera mr-2"></i>
                                Photography and Media
                            </h4>
                            <p class="text-sm text-gray-700">By visiting Calaia Farm, you consent to the use of photographs and videos taken during your visit for promotional purposes, unless you specifically request otherwise.</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-linear-to-r from-amber-50 to-orange-50 p-8 rounded-xl shadow-md mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-envelope text-amber-600"></i>
                        </div>
                        Contact Information
                    </h2>

                    <p class="text-gray-600 leading-relaxed mb-6">If you have any questions about these Terms and Conditions, please contact us:</p>

                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-envelope text-blue-600"></i>
                            </div>
                            <p class="font-medium text-gray-800">Email</p>
                            <a href="mailto:calaia.ph@gmail.com" class="text-amber-800 hover:text-amber-700 text-sm">calaia.ph@gmail.com</a>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-phone text-green-600"></i>
                            </div>
                            <p class="font-medium text-gray-800">Phone</p>
                            <p class="text-amber-800 text-sm">+63 949 139 4912</p>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-map-marker-alt text-purple-600"></i>
                            </div>
                            <p class="font-medium text-gray-800">Address</p>
                            <p class="text-gray-600 text-sm">Purok 3, Brgy. Bisaya<br>Calaca, Batangas<br>Philippines 4212</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Message -->
                <div class="text-center bg-white rounded-xl shadow-md p-8">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-heart text-amber-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Thank You for Choosing Calaia Farm</h3>
                    <p class="text-gray-600 leading-relaxed">We look forward to providing you with a memorable and enjoyable experience at our sustainable farm retreat.</p>
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-sm text-gray-500 italic"><strong>Last Updated:</strong> January 5, 2026</p>
                        <p class="text-sm text-gray-500 italic mt-2">These terms are governed by the laws of the Republic of the Philippines.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-page-layout>