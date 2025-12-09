<x-page-layout
    title="Calaia Farm - Terms of Service"
    description="Read the terms of service for Calaia Farm. Understand our terms and conditions for using our website and services."
    keywords="Calaia Farm, terms of service, terms and conditions, website terms, farm services, Philippines farm"
    active-header="terms-service"
>
    <!-- Hero Section -->
    <section class="relative h-[60vh] bg-cover bg-center flex items-center justify-center text-white overflow-hidden"
             style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset('images/facilities/terms-hero.jpg') }}')">
        <div class="absolute inset-0 bg-linear-to-br from-amber-900/40 to-amber-700/40"></div>
        <div class="relative z-10 text-center px-4 animate-fade-in">
            <h1 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">Terms of Service</h1>
            <p class="text-lg md:text-xl max-w-2xl mx-auto drop-shadow-md">
                Guidelines and terms for using Calaia Farm's website and services.
            </p>
        </div>
    </section>

    <!-- Terms of Service Section -->
    <div class="policy-subnav relative z-20 -mt-12">
        <div class="policy-container">
            <div class="flex justify-center gap-3">
                <a href="{{ url('/privacy-notice') }}" class="policy-pill secondary" @if(request()->is('privacy-notice')) aria-current="page" @endif>Privacy Notice</a>
                <a href="{{ url('/calaia-policy') }}" class="policy-pill secondary" @if(request()->is('calaia-policy')) aria-current="page" @endif>Calaia Policy</a>
                <a href="{{ url('/cookies-policy') }}" class="policy-pill secondary" @if(request()->is('cookies-policy')) aria-current="page" @endif>Cookies Policy</a>
                <a href="{{ url('/terms-service') }}" class="policy-pill" @if(request()->is('terms-service')) aria-current="page" @endif>Terms of Service</a>
            </div>
        </div>
    </div>
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="max-w-5xl mx-auto">

                <h1 class="text-4xl md:text-6xl font-bold text-center mb-12 text-gray-800 border-b-4 border-amber-800 pb-6">Terms of Service</h1>

                <p class="text-center text-gray-600 mb-8"><strong>Last Updated:</strong> {{ date('F j, Y') }}</p>

                <!-- Introduction -->
                <div class="bg-amber-50 p-6 rounded-xl mb-8 border-l-4 border-amber-800">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-file-contract text-amber-600"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-amber-800">Agreement to Terms</h2>
                    </div>
                    <p class="leading-relaxed">Welcome to Calaia Farm. These Terms of Service ("Terms") govern your use of our website located at www.calaiafarm.com and our related services (collectively, the "Service"). By accessing or using our Service, you agree to be bound by these Terms.</p>
                    <p class="leading-relaxed mt-3">If you disagree with any part of these terms, then you may not access the Service.</p>
                </div>

                <!-- Service Description -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-info-circle text-blue-600"></i>
                        </div>
                        Description of Service
                    </h2>
                    <p class="text-gray-600 leading-relaxed mb-6">Calaia Farm provides an online platform for booking farm experiences, accommodations, dining reservations, and related services. Our Service includes:</p>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span class="text-gray-700">Farm visit bookings and reservations</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span class="text-gray-700">Accommodation bookings</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span class="text-gray-700">Dining and event reservations</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span class="text-gray-700">Information about farm activities</span>
                        </div>
                    </div>
                </div>

                <!-- User Accounts -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-user-circle text-green-600"></i>
                        </div>
                        User Accounts
                    </h2>
                    <div class="space-y-4">
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-green-800 mb-2">Account Creation</h3>
                            <p class="text-gray-700 text-sm">When you create an account with us, you must provide information that is accurate, complete, and current at all times. You are responsible for safeguarding the password and for all activities that occur under your account.</p>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-blue-800 mb-2">Account Responsibilities</h3>
                            <p class="text-gray-700 text-sm">You may not use as a username the name of another person or entity that is not lawfully available for use, or a name that is otherwise offensive, vulgar, or obscene.</p>
                        </div>
                    </div>
                </div>

                <!-- Acceptable Use -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        Acceptable Use
                    </h2>
                    <p class="text-gray-600 leading-relaxed mb-6">You agree not to use the Service:</p>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <ul class="text-sm text-gray-700 space-y-2">
                                <li>• For any unlawful purpose or to solicit others to perform unlawful acts</li>
                                <li>• To violate any international, federal, provincial, or state regulations, rules, laws, or local ordinances</li>
                                <li>• To infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
                                <li>• To harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
                            </ul>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <ul class="text-sm text-gray-700 space-y-2">
                                <li>• To submit false or misleading information</li>
                                <li>• To upload or transmit viruses or any other type of malicious code</li>
                                <li>• To spam, phish, pharm, pretext, spider, crawl, or scrape</li>
                                <li>• For any obscene or immoral purpose</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Content and Intellectual Property -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-copyright text-purple-600"></i>
                        </div>
                        Content and Intellectual Property
                    </h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-purple-800 mb-2">Our Content</h3>
                            <p class="text-gray-700 text-sm">The Service and its original content, features, and functionality are and will remain the exclusive property of Calaia Farm and its licensors. The Service is protected by copyright, trademark, and other laws.</p>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-blue-800 mb-2">User Content</h3>
                            <p class="text-gray-700 text-sm">By posting content to the Service, you grant us the right and license to use, modify, publicly perform, publicly display, reproduce, and distribute such content on and through the Service.</p>
                        </div>
                    </div>
                </div>

                <!-- Termination -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-times-circle text-orange-600"></i>
                        </div>
                        Termination
                    </h2>
                    <p class="text-gray-600 leading-relaxed mb-4">We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>
                    <p class="text-gray-600 leading-relaxed">Upon termination, your right to use the Service will cease immediately. If you wish to terminate your account, you may simply discontinue using the Service.</p>
                </div>

                <!-- Limitation of Liability -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-shield-alt text-gray-600"></i>
                        </div>
                        Limitation of Liability
                    </h2>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <p class="text-gray-700 leading-relaxed">In no event shall Calaia Farm, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your use of the Service.</p>
                    </div>
                </div>

                <!-- Governing Law -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-gavel text-indigo-600"></i>
                        </div>
                        Governing Law
                    </h2>
                    <p class="text-gray-600 leading-relaxed">These Terms shall be interpreted and governed by the laws of the Republic of the Philippines, without regard to its conflict of law provisions.</p>
                    <p class="text-gray-600 leading-relaxed mt-3">Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights.</p>
                </div>

                <!-- Changes to Terms -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-edit text-teal-600"></i>
                        </div>
                        Changes to Terms
                    </h2>
                    <p class="text-gray-600 leading-relaxed">We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will try to provide at least 30 days notice prior to any new terms taking effect.</p>
                    <p class="text-gray-600 leading-relaxed mt-3">By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms.</p>
                </div>

                <!-- Contact Information -->
                <div class="bg-linear-to-r from-amber-50 to-orange-50 p-8 rounded-xl shadow-md mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-envelope text-amber-600"></i>
                        </div>
                        Contact Us
                    </h2>

                    <p class="text-gray-600 leading-relaxed mb-6">If you have any questions about these Terms of Service, please contact us:</p>

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
                        <i class="fas fa-handshake text-amber-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Thank You for Using Calaia Farm</h3>
                    <p class="text-gray-600 leading-relaxed">By using our Service, you acknowledge that you have read and understood these Terms of Service and agree to be bound by them.</p>
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-sm text-gray-500 italic"><strong>Last Updated:</strong> {{ date('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-page-layout>