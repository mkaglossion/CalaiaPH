<x-page-layout
    title="Privacy Notice - Calaia Farm"
    description="Learn about how Calaia Farm collects, uses, and protects your personal data. Our privacy policy outlines our commitment to data security and user rights."
    keywords="Calaia Farm, privacy policy, data protection, personal data, privacy notice, Philippines farm"
    active-header="privacy-notice"
>
    <!-- Hero Section -->
    <section class="relative h-[60vh] bg-cover bg-center flex items-center justify-center text-white overflow-hidden"
             style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset('images/facilities/privacy-hero.jpg') }}')">
        <div class="absolute inset-0 bg-linear-to-br from-amber-900/40 to-amber-700/40"></div>
        <div class="relative z-10 text-center px-4 animate-fade-in">
            <h1 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">Privacy Notice</h1>
            <p class="text-lg md:text-xl max-w-2xl mx-auto drop-shadow-md">
                Your privacy matters to us. Learn how we protect and handle your personal data.
            </p>
        </div>
    </section>

    <div class="policy-subnav relative z-20 -mt-12">
        <div class="policy-container">
                <div class="flex justify-center gap-3">
                <a href="{{ url('/privacy-notice') }}" class="policy-pill" @if(request()->is('privacy-notice')) aria-current="page" @endif>Privacy Notice</a>
                <a href="{{ url('/calaia-policy') }}" class="policy-pill secondary" @if(request()->is('calaia-policy')) aria-current="page" @endif>Calaia Policy</a>
                <a href="{{ url('/cookies-policy') }}" class="policy-pill secondary" @if(request()->is('cookies-policy')) aria-current="page" @endif>Cookies Policy</a>
                <a href="{{ url('/terms-service') }}" class="policy-pill secondary" @if(request()->is('terms-service')) aria-current="page" @endif>Terms of Service</a>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Section -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="max-w-5xl mx-auto">

                <h1 class="text-4xl md:text-6xl font-bold text-center mb-12 text-gray-800 border-b-4 border-amber-800 pb-6">Privacy Policy</h1>

                <p class="text-center text-gray-600 mb-8"><strong>Last Updated:</strong> {{ date('F j, Y') }}</p>

                <div class="bg-amber-50 p-6 rounded-lg mb-8 border-l-4 border-amber-800">
                    <p class="text-lg leading-relaxed mb-4"><strong>Introduction</strong></p>
                    <p class="leading-relaxed">Calaia Farm ("Calaia", "we", "us", "our") respects your privacy, and is committed to protecting the privacy, confidentiality and security of the personal data you provide to us or that we collect about you when you use our website www.calaiafarm.com or our mobile application(s) and other online products and services ("Site"), when you contact guest services, or when you otherwise interact with us. We are aware of our responsibilities to protect your personal data, to keep it secure and comply with applicable privacy and data protection laws.</p>
                </div>

                <!-- Overview Cards -->
                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-info-circle text-blue-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Policy Overview</h3>
                        </div>
                        <p class="text-gray-600 leading-relaxed">This Privacy Policy explains our personal data practices and the choices you can make about the way your personal data is used. This Privacy Policy gives effect to our commitment to protect your personal data and serves as the guidelines to be observed by all of our farm operations and services.</p>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-handshake text-green-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Your Consent</h3>
                        </div>
                        <p class="text-gray-600 leading-relaxed">You will be asked to consent to the terms of this Privacy Policy when making a reservation, registering for events or promotions, or otherwise corresponding with us via the Site or otherwise where required under applicable law.</p>
                    </div>
                </div>

                <!-- Contact Information Card -->
                <div class="bg-white p-6 rounded-xl shadow-md mb-8">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-map-marker-alt text-amber-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Data Controller</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-3">Calaia Farm, located at Purok 3, Brgy. Bisaya, Calaca, Batangas, Philippines 4212, is responsible for using your personal data.</p>
                    <p class="text-gray-600">Our data protection team can be contacted via <a href="mailto:calaia.ph@gmail.com" class="text-amber-800 hover:text-amber-700 font-medium">calaia.ph@gmail.com</a>.</p>
                </div>

                <h2 class="text-3xl font-bold text-gray-800 mb-6 border-l-4 border-amber-800 pl-4">Types of Personal Data We Collect</h2>

                <h3 class="text-2xl font-semibold text-amber-800 mb-4">Information you provide to us:</h3>
                <p class="leading-relaxed mb-4">We collect personal data you provide directly to us. The personal data collected by us include:</p>
                <ul class="list-disc list-inside space-y-2 mb-6 text-gray-700">
                    <li>Your full name, gender, date of birth, contact information (e.g. address, email address and phone number), identification information</li>
                    <li>Guest visit information, including the activities you have participated in, date of arrival and departure, goods and services purchased, special requests made, your service preferences</li>
                    <li>Your payment details and other payment information</li>
                    <li>Your membership information, account details, profile or password details</li>
                    <li>Any information necessary to fulfill special requests (for example, dietary preferences, accessibility requirements)</li>
                    <li>Your reviews, feedback, opinions and interactions you have with us about our farm, activities and services</li>
                    <li>Any other personal data you choose to provide to us (e.g. health data for accessibility needs)</li>
                </ul>

                <h3 class="text-2xl font-semibold text-amber-800 mb-4">Information We Collect Automatically When You Use the Site:</h3>
                <p class="leading-relaxed mb-4">When you access or use the Site, we automatically collect personal data about you, including:</p>
                <ul class="list-disc list-inside space-y-2 mb-6 text-gray-700">
                    <li><strong>Log Information:</strong> We may collect system log information about your use of the Site, including the type of browser you use, access times, pages viewed, your IP address</li>
                    <li><strong>Device Information:</strong> We may collect information about the computer or mobile device you use to access our Site, including the hardware model, operating system and version, unique device identifiers</li>
                    <li><strong>Location Information:</strong> We may collect information about the location of your device each time you access or use one of our mobile applications or otherwise consent to the collection of this information</li>
                    <li><strong>Information Collected by Cookies and Other Tracking Technologies:</strong> We and our service providers use various technologies to collect information, including cookies and web beacons</li>
                </ul>

                <h3 class="text-2xl font-semibold text-amber-800 mb-4">Information We Collect From Other Sources:</h3>
                <p class="leading-relaxed mb-6">We may also obtain personal data from third party service providers or from public sources and combine that with information we collect through our Site where we believe that it is necessary to help manage our relationship with you.</p>

                <h2 class="text-3xl font-bold text-gray-800 mb-8 border-l-4 border-amber-800 pl-4">How We Collect, Use and Disclose Your Personal Data</h2>

                <p class="leading-relaxed mb-6">Subject to the requirements of applicable law, we may collect, use and disclose your personal data for the purposes set out below:</p>

                <h3 class="text-2xl font-semibold text-amber-800 mb-4">For the performance of our agreement with you, in order to:</h3>
                <ul class="list-disc list-inside space-y-2 mb-6 text-gray-700">
                    <li>Process, confirm, provide and charge for farm activities, dining reservations and our goods and services</li>
                    <li>Fulfill contractual obligations to you and vendors</li>
                    <li>Provide you with access to the content on our Site, and respond to your enquiries and requests for information and services</li>
                    <li>Administer contests and promotions conducted by us or on our behalf</li>
                </ul>

                <h3 class="text-2xl font-semibold text-amber-800 mb-4">For our legitimate commercial interests, in order to:</h3>
                <ul class="list-disc list-inside space-y-2 mb-6 text-gray-700">
                    <li>Understand how our products and services impact you, provide you with a better, more personalised level of service, and further develop our products and services</li>
                    <li>Provide privileges, benefits and services to you, process applications for and administer membership programmes</li>
                    <li>Monitor your use of our Site and your bookings, and conduct analysis of the use of our Site in order to operate, evaluate, protect and improve our Site and our services</li>
                    <li>Conduct market analysis, market research, customer satisfaction and quality assurance surveys to improve our farm, activities and services</li>
                    <li>Manage and provide for the safety and security of guests, premises and services</li>
                </ul>

                <h3 class="text-2xl font-semibold text-amber-800 mb-4">For compliance with legal obligations to which we are subject, in order to:</h3>
                <ul class="list-disc list-inside space-y-2 mb-6 text-gray-700">
                    <li>Meet legal and regulatory requirements and administer general record keeping</li>
                    <li>Prevent, detect and investigate crime and analyse and manage commercial risks</li>
                    <li>Conduct investigations</li>
                </ul>

                <h3 class="text-2xl font-semibold text-amber-800 mb-4">Use of information based on your consent:</h3>
                <ul class="list-disc list-inside space-y-2 mb-6 text-gray-700">
                    <li>To facilitate direct marketing, promotional and customer management purposes, including sending you promotional communications if you have consented to receive the same</li>
                    <li>For any other purposes for which we have obtained your consent, in accordance with the requirements of applicable law</li>
                </ul>

                <!-- Data Sharing Section -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-share-alt text-red-600"></i>
                        </div>
                        Who We Share Your Data With
                    </h2>

                    <p class="text-gray-600 mb-8 leading-relaxed">Subject to the provisions of any applicable law, we may share your personal data with the following parties:</p>

                    <!-- Sharing Categories -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-credit-card text-blue-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">Payment Processors</h4>
                            </div>
                            <p class="text-sm text-gray-600">Third-party payment processors and service providers for transaction processing.</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-plane text-green-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">Travel Partners</h4>
                            </div>
                            <p class="text-sm text-gray-600">Travel agents, group organizers, and partners involved in your arrangements.</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-gavel text-purple-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">Legal Authorities</h4>
                            </div>
                            <p class="text-sm text-gray-600">Law enforcement, courts, and regulatory bodies when required by law.</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-handshake text-amber-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">Business Partners</h4>
                            </div>
                            <p class="text-sm text-gray-600">Investors, transferees, and partners in business transactions.</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-user-tie text-indigo-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">Professional Advisors</h4>
                            </div>
                            <p class="text-sm text-gray-600">Accountants, auditors, lawyers, and other professional advisors.</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-user-check text-teal-600"></i>
                                </div>
                                <h4 class="font-semibold text-gray-800">With Your Consent</h4>
                            </div>
                            <p class="text-sm text-gray-600">Any other parties at your explicit consent or direction.</p>
                        </div>
                    </div>
                </div>

                <h2 class="text-3xl font-bold text-gray-800 mb-8 border-l-4 border-amber-800 pl-4">Direct Marketing</h2>

                <p class="leading-relaxed mb-6">From time to time, we would like to use your name, email address, and other relevant contact information to send you information that we think may be of interest to you, including about our farm activities, products and services, events, offers and promotions, but we can only do so with your consent.</p>

                <p class="leading-relaxed mb-8">You may choose not to receive marketing communications or opt-out from receiving marketing communications at any time, free of charge, by following the unsubscribe instructions contained in the marketing communications or contacting us at <a href="mailto:calaia.ph@gmail.com" class="text-amber-800 hover:text-amber-700">calaia.ph@gmail.com</a>.</p>

                <h2 class="text-3xl font-bold text-gray-800 mb-6 border-l-4 border-amber-800 pl-4">Retention of Personal Data</h2>

                <p class="leading-relaxed mb-8">To the extent required or permitted by law, we take reasonable steps to delete, destroy, or de-identify personal data in a secure manner when retention is no longer necessary for legal or business purposes for which it was collected. In any event, we do not retain your personal data for longer than six (6) years, subject to local laws and regulations.</p>

                <h2 class="text-3xl font-bold text-gray-800 mb-8 border-l-4 border-amber-800 pl-4">Our Commitment to Data Security</h2>

                <p class="leading-relaxed mb-6">We have in place reasonable technical and organisational measures to prevent unauthorised or accidental access, processing, erasure, loss or use of your personal data and to keep your personal data confidential. These measures are subject to ongoing review and monitoring.</p>

                <p class="leading-relaxed mb-8">We cannot guarantee that our Site will function faultless and without any interruptions. We shall not be liable for damages that may result from the use of electronic means of communication, including, but not limited to, damages resulting from the failure or delay in delivery of electronic communications, interception or manipulation of electronic communications by third parties or by computer programs used for electronic communications and transmission of viruses.</p>

                <h2 class="text-3xl font-bold text-gray-800 mb-6 border-l-4 border-amber-800 pl-4">Children and Minors</h2>

                <p class="leading-relaxed mb-6">Except where required by local laws, we do not knowingly collect personal data from minors. If you are a minor, you may only use our Site and services with the permission of your parent or guardian.</p>

                <p class="leading-relaxed mb-8">If you are in the Philippines, our online services are not directed at children under the age of 18. If you believe we have collected information about a child under the age of 18, please contact us so that we may take appropriate steps to delete such information.</p>

                <h2 class="text-3xl font-bold text-gray-800 mb-6 border-l-4 border-amber-800 pl-4">Third Party Sites</h2>

                <p class="leading-relaxed mb-8">The Site may contain links to other websites, apps, content, services or resources on the internet which are operated by third parties. If you access other websites using the links provided, please be aware they may have their own privacy policy, and we do not accept any responsibility or liability for these policies or for any personal data which may be collected through these sites. Please check these policies before you submit any personal information to these sites.</p>

                <!-- Your Rights Section -->
                <div class="bg-white rounded-xl shadow-md p-8 mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-shield-alt text-blue-600"></i>
                        </div>
                        Your Data Protection Rights
                    </h2>

                    <p class="text-gray-600 leading-relaxed mb-8">Subject to applicable law, you have several rights regarding your personal data. We are committed to respecting and protecting these rights.</p>

                    <!-- Rights Grid -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-eye text-blue-600"></i>
                                </div>
                                <h4 class="font-semibold text-blue-800">Access</h4>
                            </div>
                            <p class="text-sm text-gray-600">Request access to your personal data we hold.</p>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-edit text-green-600"></i>
                                </div>
                                <h4 class="font-semibold text-green-800">Rectification</h4>
                            </div>
                            <p class="text-sm text-gray-600">Request correction of inaccurate or incomplete data.</p>
                        </div>

                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-trash-alt text-red-600"></i>
                                </div>
                                <h4 class="font-semibold text-red-800">Erasure</h4>
                            </div>
                            <p class="text-sm text-gray-600">Request deletion of your personal data in certain circumstances.</p>
                        </div>

                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-ban text-purple-600"></i>
                                </div>
                                <h4 class="font-semibold text-purple-800">Restriction</h4>
                            </div>
                            <p class="text-sm text-gray-600">Request limitation on how we process your data.</p>
                        </div>

                        <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-exchange-alt text-amber-600"></i>
                                </div>
                                <h4 class="font-semibold text-amber-800">Portability</h4>
                            </div>
                            <p class="text-sm text-gray-600">Request transfer of your data to another controller.</p>
                        </div>

                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-times text-indigo-600"></i>
                                </div>
                                <h4 class="font-semibold text-indigo-800">Withdraw Consent</h4>
                            </div>
                            <p class="text-sm text-gray-600">Withdraw consent for marketing communications.</p>
                        </div>
                    </div>

                    <!-- Contact for Rights -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-envelope mr-2 text-gray-600"></i>
                            How to Exercise Your Rights
                        </h3>
                        <p class="text-gray-600 leading-relaxed mb-4">To exercise any of these rights or for questions about how we handle your personal data:</p>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-amber-600 mr-3"></i>
                                <div>
                                    <p class="font-medium text-gray-800">Email</p>
                                    <a href="mailto:calaia.ph@gmail.com" class="text-amber-800 hover:text-amber-700">calaia.ph@gmail.com</a>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt text-amber-600 mr-3"></i>
                                <div>
                                    <p class="font-medium text-gray-800">Address</p>
                                    <p class="text-sm text-gray-600">Purok 3, Brgy. Bisaya, Calaca, Batangas, Philippines 4212</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h2 class="text-3xl font-bold text-gray-800 mb-6 border-l-4 border-amber-800 pl-4">Changes to the Privacy Policy</h2>

                <p class="leading-relaxed mb-6">We may modify this Privacy Policy from time to time. Any changes to this Privacy Policy will be posted to the Site so that you are always informed of the way we collect and use your personal data. Updated versions of this Privacy Policy will include the date of the revision at the end of this Privacy Policy so that you are able to check when the Privacy Policy was last amended.</p>

                <p class="leading-relaxed mb-8">Any changes to this Privacy Policy will become effective upon posting of the revised Privacy Policy on the Site. Use of the Site following such changes constitutes your acceptance of the revised Privacy Policy then in effect.</p>

                <h2 class="text-3xl font-bold text-gray-800 mb-6 border-l-4 border-amber-800 pl-4">Contact Information</h2>

                <p class="leading-relaxed mb-6">If you have any questions about this Privacy Policy or our privacy practices, please contact us:</p>

                <div class="bg-gray-50 p-8 rounded-lg mb-8">
                    <p class="mb-2"><strong>Email:</strong> <a href="mailto:calaia.ph@gmail.com" class="text-amber-800 hover:text-amber-700">calaia.ph@gmail.com</a></p>
                    <p class="mb-2"><strong>Phone:</strong> +63 949 139 4912</p>
                    <p class="mb-6"><strong>Address:</strong> Purok 3, Brgy. Bisaya, Calaca, Batangas, Philippines 4212</p>
                </div>

                <div class="text-center mt-12 pt-8 border-t border-gray-300">
                    <p class="text-gray-600 italic"><strong>Last Updated:</strong> {{ date('F j, Y') }}</p>
                    <p class="text-gray-600 italic">This Privacy Policy is governed by and shall be construed in accordance with the laws of the Republic of the Philippines.</p>
                </div>
            </div>
        </div>
    </section>
</x-page-layout>