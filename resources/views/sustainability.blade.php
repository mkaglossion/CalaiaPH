<x-page-layout
    title="Sustainability - Calaia Farm"
    description="Discover Calaia Farm's commitment to sustainable agriculture and environmental stewardship. Learn about our organic farming practices, water conservation efforts, and community initiatives."
    keywords="Calaia Farm, sustainability, organic farming, environmental stewardship, water conservation, sustainable agriculture, Philippines farm"
    active-header="sustainability"
>
    <!-- Hero Section -->
    <header class="relative h-[70vh] bg-cover bg-center flex items-center justify-center text-white overflow-hidden"
            role="banner"
            aria-labelledby="hero-title">
        <img src="{{ \App\Helpers\ImageHelper::src('images/carousel/animal and wellness.jpg') }}" alt="Beautiful landscape showcasing Calaia Farm's sustainable agriculture and environmental commitment" class="absolute inset-0 w-full h-full object-cover" loading="eager" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
        <div class="absolute inset-0 bg-linear-to-br from-green-900/30 to-green-700/30 animate-pulse"></div>
        <div class="relative z-10 text-center px-4 animate-fade-in">
            <h1 id="hero-title" class="text-5xl md:text-6xl font-bold mb-4 drop-shadow-lg">Sustainability</h1>
            <p class="text-xl md:text-2xl max-w-2xl mx-auto drop-shadow-md">
                Founded on the principles of environmental stewardship, Calaia Farm is committed to sustainable practices that ensure our land remains thriving for future generations.
            </p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-16" role="main">
        <div class="policy-container px-4 py-8">
            <div class="prose prose-lg max-w-4xl mx-auto text-gray-700">

                <!-- Environmental Commitment -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 border-l-4 border-green-800 pl-4 mt-12">Our Environmental Commitment</h2>
                    <article class="bg-white p-8 rounded-lg shadow-md mb-12 hover:shadow-xl transition-shadow duration-300">
                        <p class="text-lg leading-relaxed">
                            At Calaia Farm, sustainability isn't just a buzzword—it's the foundation of everything we do. We believe that responsible farming practices can heal the earth while providing for our community. Our approach integrates traditional wisdom with modern sustainable techniques to create a model that others can follow.
                        </p>
                    </article>
                </section>

                <!-- Sustainability Pillars -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 border-l-4 border-green-800 pl-4 mt-16">Our Sustainability Pillars</h2>
                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
                        <article class="text-center bg-linear-to-br from-amber-800 to-amber-600 p-8 rounded-lg shadow-md text-white hover:scale-105 transition-transform duration-300">
                            <div class="text-4xl font-bold mb-2">100%</div>
                            <p class="text-lg">Organic Farming</p>
                        </article>
                        <article class="text-center bg-linear-to-br from-amber-800 to-amber-600 p-8 rounded-lg shadow-md text-white hover:scale-105 transition-transform duration-300">
                            <div class="text-4xl font-bold mb-2">Zero</div>
                            <p class="text-lg">Chemical Pesticides</p>
                        </article>
                        <article class="text-center bg-linear-to-br from-amber-800 to-amber-600 p-8 rounded-lg shadow-md text-white hover:scale-105 transition-transform duration-300">
                            <div class="text-4xl font-bold mb-2">50%</div>
                            <p class="text-lg">Water Conservation</p>
                        </article>
                        <article class="text-center bg-linear-to-br from-amber-800 to-amber-600 p-8 rounded-lg shadow-md text-white hover:scale-105 transition-transform duration-300">
                            <div class="text-4xl font-bold mb-2">80%</div>
                            <p class="text-lg">Local Sourcing</p>
                        </article>
                    </div>
                </section>

                <!-- Key Sustainability Initiatives -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 border-l-4 border-green-800 pl-4 mt-16">Key Sustainability Initiatives</h2>
                    <div class="grid md:grid-cols-2 gap-8 mb-16">
                        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:scale-105 transition-transform duration-300">
                            <img src="{{ \App\Helpers\ImageHelper::src('images/carousel/bonfire-5.jpg') }}" alt="Organic farming practices at Calaia Farm" class="w-full h-48 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-green-800 mb-4">Organic Farming Practices</h3>
                                <p class="text-gray-600 leading-relaxed">
                                    We use only natural methods to maintain soil health and control pests. Our composting system returns nutrients to the soil, creating a closed-loop system that builds rather than depletes our land.
                                </p>
                            </div>
                        </article>

                        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:scale-105 transition-transform duration-300">
                            <img src="{{ \App\Helpers\ImageHelper::src('images/carousel/calaia1.jpg') }}" alt="Water conservation systems at Calaia Farm" class="w-full h-48 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-green-800 mb-4">Water Conservation</h3>
                                <p class="text-gray-600 leading-relaxed">
                                    Our innovative rainwater harvesting and drip irrigation systems reduce water usage by half while ensuring our crops get the moisture they need. Every drop counts in our conservation efforts.
                                </p>
                            </div>
                        </article>

                        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:scale-105 transition-transform duration-300">
                            <img src="{{ \App\Helpers\ImageHelper::src('images/carousel/animal and wellness.jpg') }}" alt="Biodiversity enhancement at Calaia Farm" class="w-full h-48 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-green-800 mb-4">Biodiversity Enhancement</h3>
                                <p class="text-gray-600 leading-relaxed">
                                    We've planted native trees and created habitats that support local wildlife. Our farm has become a sanctuary for birds, butterflies, and beneficial insects that help maintain ecological balance.
                                </p>
                            </div>
                        </article>

                        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:scale-105 transition-transform duration-300">
                            <img src="{{ \App\Helpers\ImageHelper::src('images/carousel/Calaia-26.jpg') }}" alt="Renewable energy systems at Calaia Farm" class="w-full h-48 object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-green-800 mb-4">Renewable Energy</h3>
                                <p class="text-gray-600 leading-relaxed">
                                    Solar panels power our facilities, and we're working towards energy independence. Our goal is to generate more energy than we consume while reducing our carbon footprint.
                                </p>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- Waste Management & Recycling -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 border-l-4 border-green-800 pl-4 mt-16">Waste Management & Recycling</h2>
                    <div class="bg-green-50 border-2 border-green-200 rounded-lg p-8 mb-8">
                        <h3 class="text-xl font-semibold text-green-800 mb-4">Zero Waste Initiative</h3>
                        <p class="text-gray-700 leading-relaxed mb-6">
                            Our comprehensive waste management system ensures that nothing goes to landfill. Food waste becomes compost, packaging is reused or recycled, and we continuously seek ways to minimize our environmental impact.
                        </p>
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Waste Reduction Strategies:</h4>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li><strong>Composting:</strong> All organic waste is composted and returned to the soil</li>
                            <li><strong>Recycling:</strong> Paper, plastic, glass, and metal are sorted and recycled</li>
                            <li><strong>Reuse:</strong> Packaging materials are reused whenever possible</li>
                            <li><strong>Food Recovery:</strong> Surplus produce is donated to local communities</li>
                        </ul>
                    </div>
                </section>

                <!-- Community & Education -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 border-l-4 border-green-800 pl-4 mt-16">Community & Education</h2>
                    <div class="bg-gray-50 py-16 mb-8">
                            <div class="policy-container px-4">
                            <div class="text-center">
                                <h3 class="text-2xl font-semibold text-green-800 mb-4">Environmental Education</h3>
                                <p class="text-lg text-gray-700 max-w-2xl mx-auto">
                                    We believe that education is key to creating lasting change. Through our workshops, farm tours, and school programs, we teach visitors about sustainable agriculture and environmental stewardship.
                                </p>
                            </div>
                        </div>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-800 mb-4">Educational Programs:</h4>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 mb-8">
                        <li><strong>Farm Tours:</strong> Guided tours explaining our sustainable practices</li>
                        <li><strong>School Programs:</strong> Educational visits for students of all ages</li>
                        <li><strong>Workshops:</strong> Hands-on learning about organic farming and conservation</li>
                        <li><strong>Community Outreach:</strong> Free seminars for local farmers and residents</li>
                    </ul>
                </section>

                <!-- Local Community Support -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 border-l-4 border-green-800 pl-4 mt-16">Local Community Support</h2>
                    <article class="bg-white p-8 rounded-lg shadow-md mb-8 hover:shadow-xl transition-shadow duration-300">
                        <p class="text-lg leading-relaxed mb-6">
                            We are deeply committed to supporting our local community. By sourcing from nearby farmers, employing local residents, and sharing our knowledge, we create a network of sustainability that extends beyond our farm boundaries.
                        </p>
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Community Initiatives:</h4>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li>Supporting local farmers through fair trade practices</li>
                            <li>Creating employment opportunities for community members</li>
                            <li>Sharing resources and knowledge with neighboring farms</li>
                            <li>Contributing to local environmental conservation efforts</li>
                        </ul>
                    </article>
                </section>

                <!-- Measuring Our Impact -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 border-l-4 border-green-800 pl-4 mt-16">Measuring Our Impact</h2>
                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
                        <article class="text-center bg-linear-to-br from-green-800 to-green-600 p-8 rounded-lg shadow-md text-white hover:scale-105 transition-transform duration-300">
                            <div class="text-4xl font-bold mb-2">5 Years</div>
                            <p class="text-lg">Carbon Neutral Operations</p>
                        </article>
                        <article class="text-center bg-linear-to-br from-green-800 to-green-600 p-8 rounded-lg shadow-md text-white hover:scale-105 transition-transform duration-300">
                            <div class="text-4xl font-bold mb-2">1000+</div>
                            <p class="text-lg">Trees Planted</p>
                        </article>
                        <article class="text-center bg-linear-to-br from-green-800 to-green-600 p-8 rounded-lg shadow-md text-white hover:scale-105 transition-transform duration-300">
                            <div class="text-4xl font-bold mb-2">500+</div>
                            <p class="text-lg">Students Educated</p>
                        </article>
                        <article class="text-center bg-linear-to-br from-green-800 to-green-600 p-8 rounded-lg shadow-md text-white hover:scale-105 transition-transform duration-300">
                            <div class="text-4xl font-bold mb-2">25</div>
                            <p class="text-lg">Local Jobs Created</p>
                        </article>
                    </div>
                </section>

                <!-- Future Goals -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 border-l-4 border-green-800 pl-4 mt-16">Future Goals</h2>
                    <div class="bg-green-50 border-2 border-green-200 rounded-lg p-8 mb-8">
                        <h3 class="text-xl font-semibold text-green-800 mb-4">Our 2030 Sustainability Targets</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li>Achieve complete energy independence through renewable sources</li>
                            <li>Expand organic farming practices to 100 hectares</li>
                            <li>Educate 5,000 students about sustainable agriculture</li>
                            <li>Achieve zero-waste operations</li>
                            <li>Create a replicable model for sustainable farming in the Philippines</li>
                        </ul>
                    </div>
                </section>

                <!-- Get Involved -->
                <section class="mb-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 border-l-4 border-green-800 pl-4 mt-16">Get Involved</h2>
                    <p class="text-lg leading-relaxed mb-6">
                        We believe that sustainability is a collective effort. Here are ways you can join us in protecting our environment:
                    </p>
                    <article class="bg-white p-8 rounded-lg shadow-md mb-8 hover:shadow-xl transition-shadow duration-300">
                        <h3 class="text-xl font-semibold text-green-800 mb-4">Ways to Participate:</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li><strong>Volunteer:</strong> Join our farm work days and conservation projects</li>
                            <li><strong>Learn:</strong> Attend our workshops and educational programs</li>
                            <li><strong>Support:</strong> Choose local, sustainable products and services</li>
                            <li><strong>Share:</strong> Spread the word about sustainable farming practices</li>
                        </ul>
                    </article>
                </section>

                <!-- Closing Message -->
                <section class="text-center mt-16">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Together, we can create a sustainable future</h2>
                    <p class="text-lg text-gray-600 mb-8">
                        Every action, no matter how small, contributes to the health of our planet.
                    </p>
                    <a href="{{ route('contact') }}" class="inline-block bg-green-800 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors duration-300 shadow-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" aria-label="Contact Calaia Farm to get involved in sustainability efforts">
                        Get Involved
                    </a>
                </section>
            </div>
        </div>
    </main>
</x-page-layout>
