<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-3 px-lg-5 mt-4">
        @php
        $sections = [
            [
                "id" => "staycation",
                "title" => "Where to Stay",
                "imagePath" => "/assets/images/carousel/Glamping-7.jpg",
                "heading" => "Glamping",
                "description1" => "Hotel-style comfort in the great outdoors—cozy beds, stylish tents, and thoughtful amenities.",
                "description2" => "Glamping, Tent Rental, BYOT, or Car Camping—four ways to stay close to nature, from plush comfort to DIY simplicity, with shared facilities and scenic spots throughout.",
                "linkPath" => "/staycation"
            ],
            [
                "id" => "activities",
                "title" => "What to Do",
                "imagePath" => "/assets/images/carousel/activities-1.jpg",
                "heading" => "Activities",
                "description1" => "Showcases all the fun and enriching activities guests can enjoy during their stay. This includes nature-based experiences, adventure rides, creative crafts, farm visits, and indoor/outdoor games — each paired with a photo and description.",
                "description2" => "From slow nature walks to splashy adventures, Calaia packs your day with things to do. Choose from guided trails, farm encounters, crafty workshops, and friendly games—each with a photo, quick brief, and what to expect.",
                "linkPath" => "/activities"
            ],
            [
                "id" => "f&b",
                "title" => "Food and Beverage",
                "imagePath" => "/assets/images/carousel/SET MEALS2.jpg",
                "heading" => "From our farm to your table",
                "description1" => "From freshly-brewed coffee to grilled meats, we have all your meals covered for a worry-free stay.",
                "description2" => "Enjoy casual dining areas, picnic-ready options, and barista-made coffee—perfect for slow mornings and golden-hour sunsets. Ask our team for today's specials and seasonal picks.",
                "linkPath" => "/dining"
            ],
            [
                "id" => "groups",
                "title" => "Groups",
                "imagePath" => "/assets/images/carousel/bonfire.jpg",
                "heading" => "Calaia Nature Park Experience",
                "description1" => "Strong and effective teams are built with purpose",
                "description2" => "Calaia Nature Park is the perfect partner for your next team building event. Our packages include comfortable stays, nourishing meals, and group activities to create stronger bonds.",
                "linkPath" => "/groups"
            ],
            [
                "id" => "farm",
                "title" => "Our Farm",
                "imagePath" => "/assets/images/carousel/animal and wellness.jpg",
                "heading" => "Farm Experience",
                "description1" => "Take a leisurely stroll around our farm where we diligently practice natural methods of farming. Taking wisdom from permaculture practices, we do not use chemical pesticides or herbicides. Our farm animals also share their expertise in pest control and fertilizer production.",
                "description2" => "Take a relaxed walk around our farm. We grow naturally—no chemical pesticides or herbicides—and our animals help with pest control and fertilizer.",
                "linkPath" => "/farm"
            ]
        ];
        @endphp

        @foreach($sections as $i => $section)
            <div class="flex flex-col {{ $i % 2 === 1 ? 'lg:flex-row-reverse' : 'lg:flex-row' }} items-center gap-8 mb-16">
                <div class="w-full lg:w-1/2">
                    <img src="{{ asset($section['imagePath']) }}" alt="{{ $section['title'] }}" class="w-full h-64 object-cover rounded-lg shadow-lg">
                </div>
                <div class="w-full lg:w-1/2 {{ $i % 2 === 1 ? 'lg:text-right' : 'lg:text-left' }} text-center">
                    <h2 class="text-3xl font-bold text-[#8f5d54] mb-4">{{ $section['title'] }}</h2>
                    <h3 class="text-xl font-semibold text-[#8f5d54] mb-4">{{ $section['heading'] }}</h3>
                    <p class="text-[#8f5d54] mb-4 leading-relaxed">{{ $section['description1'] }}</p>
                    <p class="text-[#8f5d54] mb-6 leading-relaxed">{{ $section['description2'] }}</p>
                    <a href="{{ $section['linkPath'] }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-[#8f5d54] hover:bg-[#8f5d54]/90 transition-colors duration-300">
                        Learn More
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>