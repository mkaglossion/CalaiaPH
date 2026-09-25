<x-page-layout
    title="Calaia Farm - Cookies Policy"
    description="Learn about how Calaia Farm uses cookies on our website. Understand our cookie policy and how to manage your cookie preferences."
    keywords="Calaia Farm, cookies policy, cookie settings, privacy, website cookies, tracking cookies, Philippines farm"
    active-header="cookies-policy"
>
    <!-- Hero Section -->
    <section class="relative h-[60vh] bg-cover bg-center flex items-center justify-center text-white overflow-hidden"
             style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset('images/facilities/cookies-hero.jpg') }}')">
        <div class="absolute inset-0 bg-linear-to-br from-amber-900/40 to-amber-700/40"></div>
        <div class="relative z-10 text-center px-4 animate-fade-in">
            <h1 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">Cookies Policy</h1>
            <p class="text-lg md:text-xl max-w-2xl mx-auto drop-shadow-md">
                Understanding how we use cookies to enhance your experience on our website.
            </p>
        </div>
    </section>

    <!-- Cookies Policy Section -->
    <div class="policy-subnav relative z-20 -mt-12">
        <div class="policy-container">
            <div class="flex justify-center gap-3">
                <a href="{{ url('/privacy-notice') }}" class="policy-pill secondary" @if(request()->is('privacy-notice')) aria-current="page" @endif>Privacy Notice</a>
                <a href="{{ url('/calaia-policy') }}" class="policy-pill secondary" @if(request()->is('calaia-policy')) aria-current="page" @endif>Calaia Policy</a>
                <a href="{{ url('/cookies-policy') }}" class="policy-pill" @if(request()->is('cookies-policy')) aria-current="page" @endif>Cookies Policy</a>
                <a href="{{ url('/terms-service') }}" class="policy-pill secondary" @if(request()->is('terms-service')) aria-current="page" @endif>Terms of Service</a>
            </div>
        </div>
    </div>
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="max-w-5xl mx-auto">

                <h1 class="text-4xl md:text-6xl font-bold text-center mb-12 text-gray-800 border-b-4 border-amber-800 pb-6">Cookies Policy</h1>

                <p class="text-center text-gray-600 mb-8"><strong>Last Updated:</strong> {{ date('F j, Y') }}</p>

                <!-- Introduction -->
                <div class="bg-amber-50 p-6 rounded-xl mb-8 border-l-4 border-amber-800">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-cookie-bite text-amber-600"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-amber-800">Introduction</h2>
                    </div>
                    <p class="leading-relaxed">This Cookies Policy is issued by Calaia Farm ("we", "us", "our"). This Cookies Policy applies to our use of cookies in connection with our website www.calaiafarm.com and our mobile application(s) (collectively "Site").</p>
                    <p class="leading-relaxed mt-3">By continuing to use our Site, you are agreeing to our use of cookies in the manner set out in this Cookies Policy.</p>
                </div>

                <!-- What This Policy Covers -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-list-check text-blue-600"></i>
                        </div>
                        What This Cookies Policy Covers
                    </h2>
                    <p class="text-gray-600 leading-relaxed mb-6">This Cookies Policy sets out:</p>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                            <span class="text-gray-700">What a cookie is</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-chart-bar text-blue-500 mt-1"></i>
                            <span class="text-gray-700">An overview of the cookies we use</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-ban text-blue-500 mt-1"></i>
                            <span class="text-gray-700">What you can do if you do not agree to the use of cookies</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-external-link-alt text-blue-500 mt-1"></i>
                            <span class="text-gray-700">Further information about cookies in general</span>
                        </div>
                    </div>
                </div>

                <!-- What Is a Cookie -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-question-circle text-purple-600"></i>
                        </div>
                        What Is a Cookie?
                    </h2>
                    <div class="bg-purple-50 p-4 rounded-lg mb-6">
                        <p class="text-gray-700 leading-relaxed">After you enter the Site, the Site will make use of "cookies". Cookies are small text files containing small amounts of information which are downloaded and may be stored on any of your internet enabled devices e.g. your computer, smartphone or tablet - like a memory for a web page.</p>
                    </div>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-shield-alt mr-2 text-green-600"></i>
                                Privacy Policy Link
                            </h4>
                            <p class="text-sm text-gray-600">This Cookies Policy provides you with information about the cookies we use and why. Our <a href="{{ route('privacy-notice') }}" class="text-amber-800 hover:text-amber-700 font-medium">Privacy Policy</a> sets out full details of the other information we may collect and how we may use your personal data.</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2 text-amber-600"></i>
                                Important Note
                            </h4>
                            <p class="text-sm text-gray-600">If you do not wish to accept cookies in connection with your use of this Site, you should stop using it or turn off your cookies, but this may affect the functionality of the Site.</p>
                        </div>
                    </div>
                </div>

                <!-- Cookies Used on the Site -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-cookie text-green-600"></i>
                        </div>
                        Cookies Used on the Site
                    </h2>

                    <p class="text-gray-600 leading-relaxed mb-8">We use several different types of cookies. In particular, we use the following cookies:</p>

                    <!-- Cookie Types Grid -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Necessary Cookies -->
                        <div class="bg-red-50 border-2 border-red-200 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-exclamation text-red-600"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-red-800">Necessary Cookies</h3>
                            </div>
                            <p class="text-gray-700 text-sm leading-relaxed">Essential cookies that help you navigate the Site. These cookies make sure your information is saved during all steps of the booking process. This helps to support security and basic functionality of the Site.</p>
                            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Required for Site Operation
                            </div>
                        </div>

                        <!-- Functional Cookies -->
                        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-cogs text-blue-600"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-blue-800">Functional Cookies</h3>
                            </div>
                            <p class="text-gray-700 text-sm leading-relaxed">Used to provide you the best user experience. These cookies save and remind you about reservations you have started to make, so will save you time if you want to complete your reservation later.</p>
                            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Enhances User Experience
                            </div>
                        </div>

                        <!-- Performance Cookies -->
                        <div class="bg-purple-50 border-2 border-purple-200 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-chart-line text-purple-600"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-purple-800">Performance Cookies</h3>
                            </div>
                            <p class="text-gray-700 text-sm leading-relaxed">Help us to understand the behaviour of our visitors and the usage of the Site. This allows us to continuously improve the Site to provide you the best user experience.</p>
                            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Analytics & Improvement
                            </div>
                        </div>

                        <!-- Advertising Cookies -->
                        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-ad text-green-600"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-green-800">Advertising Cookies</h3>
                            </div>
                            <p class="text-gray-700 text-sm leading-relaxed">Will remember your preferences and that you visited the Site. We strive to provide you with advertisements for farm activities and other products relevant to your interests.</p>
                            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Personalized Advertising
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cookie Details Tables -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-table text-indigo-600"></i>
                        </div>
                        Cookie Details
                    </h2>

                    <p class="text-gray-600 leading-relaxed mb-8">More specifically, we may use the following cookies on our Site:</p>

                    <!-- Performance Cookies Table -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-purple-800 mb-4 flex items-center">
                            <i class="fas fa-chart-line mr-2"></i>
                            Performance Cookies
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-gray-300 rounded-lg overflow-hidden">
                                <thead class="bg-purple-100">
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-purple-800">Cookie Name</th>
                                        <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-purple-800">Purpose</th>
                                        <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-purple-800">Duration</th>
                                        <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-purple-800">Provider</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-3 font-medium">Google Analytics</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">To help analyse which pages on our website visitors viewed</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">1 day to 2 years</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">Google Inc</td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-3 font-medium">Adobe Analytics</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">To help analyse visitors' activities on our website</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">Session to 49 months</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">Adobe</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Necessary Cookies Table -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-red-800 mb-4 flex items-center">
                            <i class="fas fa-exclamation mr-2"></i>
                            Necessary Cookies
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-gray-300 rounded-lg overflow-hidden">
                                <thead class="bg-red-100">
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-red-800">Cookie Name</th>
                                        <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-red-800">Purpose</th>
                                        <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-red-800">Duration</th>
                                        <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-red-800">Provider</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-3 font-medium">ASP.NET_SessionId</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">To identify user sessions on our website</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">Session</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">Microsoft</td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-3 font-medium">.ASPXFORMSAUTH</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">For authentication on our website's logon page</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">Session</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">Microsoft</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Functional Cookies Table -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-blue-800 mb-4 flex items-center">
                            <i class="fas fa-cogs mr-2"></i>
                            Functional Cookies
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-gray-300 rounded-lg overflow-hidden">
                                <thead class="bg-blue-100">
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-blue-800">Cookie Name</th>
                                        <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-blue-800">Purpose</th>
                                        <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-blue-800">Duration</th>
                                        <th class="border border-gray-300 px-4 py-3 text-left font-semibold text-blue-800">Provider</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-300 px-4 py-3 font-medium">Session Cookies</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">To remember user preferences and login status</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">Session</td>
                                        <td class="border border-gray-300 px-4 py-3 text-sm">Calaia Farm</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Control Your Cookie Settings -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-sliders-h text-teal-600"></i>
                        </div>
                        Control Your Cookie Settings
                    </h2>

                    <div class="bg-teal-50 p-6 rounded-lg mb-6">
                        <p class="text-gray-700 leading-relaxed mb-4">We shall store a cookie on your computer or device to remember your cookie preferences for next time. You can change your browser settings so that you receive a warning before certain cookies are stored. You can also adjust your settings so that your browser refuses most of our cookies or only certain cookies from third parties.</p>
                        <p class="text-gray-700 leading-relaxed">If you wish to withdraw your agreement at any time, you will need to delete our cookies using your internet browser settings. You should do this through the browser settings for each browser you use.</p>
                    </div>

                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-amber-600 mt-1 mr-3"></i>
                            <div>
                                <p class="text-gray-800 font-medium mb-1">Important Notice</p>
                                <p class="text-gray-700 text-sm">Please be aware that some of our services will not function if your browser does not accept cookies. However, you can allow cookies from specific websites by making them "trusted websites" in your internet browser.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Browser Settings Guides -->
                    <div>
                        <h3 class="text-xl font-semibold text-teal-800 mb-6 flex items-center">
                            <i class="fas fa-browser mr-2"></i>
                            Browser Settings Guides
                        </h3>
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <a href="https://support.microsoft.com/en-us/help/17442/windows-internet-explorer-delete-manage-cookies" target="_blank" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200 hover:border-blue-300">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fab fa-internet-explorer text-blue-600"></i>
                                    </div>
                                    <span class="font-medium text-gray-800">Internet Explorer</span>
                                </div>
                                <p class="text-sm text-gray-600">Manage Cookies Guide</p>
                            </a>

                            <a href="http://support.mozilla.com/en-US/kb/Cookies" target="_blank" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200 hover:border-orange-300">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fab fa-firefox text-orange-600"></i>
                                    </div>
                                    <span class="font-medium text-gray-800">Mozilla Firefox</span>
                                </div>
                                <p class="text-sm text-gray-600">Cookie Information</p>
                            </a>

                            <a href="http://www.google.com/support/chrome/bin/answer.py?hl=en&answer=95647" target="_blank" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200 hover:border-red-300">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fab fa-chrome text-red-600"></i>
                                    </div>
                                    <span class="font-medium text-gray-800">Google Chrome</span>
                                </div>
                                <p class="text-sm text-gray-600">Chrome Help</p>
                            </a>

                            <a href="http://support.apple.com/kb/PH5042" target="_blank" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200 hover:border-gray-300">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fab fa-safari text-gray-600"></i>
                                    </div>
                                    <span class="font-medium text-gray-800">Safari</span>
                                </div>
                                <p class="text-sm text-gray-600">Safari Help</p>
                            </a>

                            <a href="http://www.opera.com/help/tutorials/security/privacy/" target="_blank" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200 hover:border-red-300">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fab fa-opera text-red-600"></i>
                                    </div>
                                    <span class="font-medium text-gray-800">Opera</span>
                                </div>
                                <p class="text-sm text-gray-600">Opera Help</p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Third Party Cookies & Additional Info -->
                <div class="grid md:grid-cols-2 gap-8 mb-8">
                    <!-- Third Party Cookies -->
                    <div class="bg-white rounded-xl shadow-md p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-users text-pink-600"></i>
                            </div>
                            Third Party Cookies
                        </h2>
                        <p class="text-gray-600 leading-relaxed mb-4">We also use third party cookies - these are cookies that are set by a third party website or applications rather than by us. The Site allows cookies of third party social networks (e.g. Facebook and LinkedIn), and this enables a user to share content of the Site on social networks.</p>
                        <p class="text-gray-600 leading-relaxed text-sm">These third parties may use cookies, but their use of cookies will not be governed by this Cookies Policy.</p>
                    </div>

                    <!-- Other Information -->
                    <div class="bg-white rounded-xl shadow-md p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-info-circle text-cyan-600"></i>
                            </div>
                            Other Information
                        </h2>
                        <p class="text-gray-600 leading-relaxed mb-4">For further information about cookies, please visit:</p>
                        <div class="space-y-2">
                            <a href="http://www.aboutcookies.org" target="_blank" class="flex items-center text-cyan-600 hover:text-cyan-800 transition-colors">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                About Cookies
                            </a>
                            <a href="http://www.youronlinechoices.eu" target="_blank" class="flex items-center text-cyan-600 hover:text-cyan-800 transition-colors">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                Your Online Choices
                            </a>
                            <a href="http://www.aboutads.info" target="_blank" class="flex items-center text-cyan-600 hover:text-cyan-800 transition-colors">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                About Ads Info
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-linear-to-r from-amber-50 to-orange-50 p-8 rounded-xl shadow-md mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-envelope text-amber-600"></i>
                        </div>
                        Contact Us
                    </h2>

                    <p class="text-gray-600 leading-relaxed mb-6">If you have any questions about the cookies used on our website, please contact us:</p>

                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-envelope text-blue-600"></i>
                            </div>
                            <p class="font-medium text-gray-800">Email</p>
                            <a href="{{ \App\Models\InformationPage::contactDetails()['email_uri'] }}" class="text-amber-800 hover:text-amber-700 text-sm">{{ \App\Models\InformationPage::contactDetails()['email'] }}</a>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm text-center">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-phone text-green-600"></i>
                            </div>
                            <p class="font-medium text-gray-800">Phone</p>
                            <a href="{{ \App\Models\InformationPage::contactDetails()['phone_uri'] }}" class="text-amber-800 hover:text-amber-700 text-sm">{{ \App\Models\InformationPage::contactDetails()['phone'] }}</a>
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

                <!-- Changes to Policy & Footer -->
                <div class="bg-white rounded-xl shadow-md p-8">
                    <div class="grid md:grid-cols-2 gap-8 mb-8">
                        <!-- Changes to Policy -->
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-edit text-indigo-600"></i>
                                </div>
                                Changes to This Policy
                            </h2>
                            <p class="text-gray-600 leading-relaxed">We may change this Cookies Policy from time to time by posting the updated version of the policy on our Site. Please check this Site periodically to be informed of any changes.</p>
                        </div>

                        <!-- Privacy Policy Link -->
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-link text-green-600"></i>
                                </div>
                                Related Policy
                            </h2>
                            <p class="text-gray-600 leading-relaxed">This Cookies Policy should be read in conjunction with our <a href="{{ route('privacy-notice') }}" class="text-amber-800 hover:text-amber-700 font-medium">Privacy Policy</a>.</p>
                        </div>
                    </div>

                    <!-- Footer Message -->
                    <div class="text-center pt-6 border-t border-gray-200">
                        <p class="text-sm text-gray-500 italic"><strong>Last Updated:</strong> {{ date('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-page-layout>