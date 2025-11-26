<section class="py-12 bg-[#f5efe7]">
    <div class="max-w-7xl mx-auto px-3 px-lg-5">
        @php
        $sections = [
            [
                "id" => "staycation",
                "title" => "Where to Stay.",
                "imagePath" => "/assets/images/carousel/wheretostay.jpg",
                "description1" => "Hotel-style comfort in the great outdoors—cozy beds, stylish tents, and thoughtful amenities.",
                "description2" => "Glamping, Tent Rental, BYOT, or Car Camping—four ways to stay close to nature, from plush comfort to DIY simplicity, with shared facilities and scenic spots throughout.",
                "linkPath" => "/staycation"
            ],
            [
                "id" => "activities",
                "title" => "What to Do.",
                "imagePath" => "/assets/images/carousel/whattodo.jpg",
                "description1" => "Showcases all the fun and enriching activities guests can enjoy during their stay. This includes nature-based experiences, adventure rides, creative crafts, farm visits, and indoor/outdoor games — each paired with a photo and description.",
                "description2" => "From slow nature walks to splashy adventures, Calaia packs your day with things to do. Choose from guided trails, farm encounters, crafty workshops, and friendly games—each with a photo, quick brief, and what to expect.",
                "linkPath" => "/activities"
            ],
            [
                "id" => "f&b",
                "title" => "Food and Beverage.",
                "imagePath" => "/assets/images/carousel/f&b.jpg",
                "description1" => "From freshly-brewed coffee to grilled meats, we have all your meals covered for a worry-free stay.",
                "description2" => "Enjoy casual dining areas, picnic-ready options, and barista-made coffee—perfect for slow mornings and golden-hour sunsets. Ask our team for today's specials and seasonal picks.",
                "linkPath" => "/dining"
            ],
            [
                "id" => "grpups",
                "title" => "Special Events.",
                "imagePath" => "/assets/images/carousel/groups.jpg",
                "description1" => "Strong and effective teams are built with purpose",
                "description2" => "Calaia Nature Park is the perfect partner for your next team building event. Our packages include comfortable stays, nourishing meals, and group activities to create stronger bonds.",
                "linkPath" => "/groups"
            ],
            [
                "id" => "farm",
                "title" => "Our Farm.",
                "imagePath" => "/assets/images/carousel/farm.jpg",
                "description1" => "Take a leisurely stroll around our farm where we diligently practice natural methods of farming. Taking wisdom from permaculture practices, we do not use chemical pesticides or herbicides. Our farm animals also share their expertise in pest control and fertilizer production.",
                "description2" => "Take a relaxed walk around our farm. We grow naturally—no chemical pesticides or herbicides—and our animals help with pest control and fertilizer.",
                "linkPath" => "/farm"
            ]
        ];
        @endphp

        @foreach($sections as $i => $section)
            <div class="flex flex-col {{ $i % 2 === 1 ? 'lg:flex-row-reverse' : 'lg:flex-row' }} items-center gap-8 lg:gap-16 mb-4 lg:mb-8">
                <div class="w-full lg:w-1/2">
                    <img src="{{ asset($section['imagePath']) }}" alt="{{ $section['title'] }}" class="w-full h-80 object-cover rounded-lg shadow-lg">
                </div>
                <div class="w-full lg:w-1/2 lg:text-left text-center flex flex-col justify-center py-6 px-4">
                    <h2 class="text-4xl font-bold text-[#8f5d54] mb-4 uppercase text-left" style="font-family: 'Gotham Book', sans-serif; font-size: 25px;">{{ $section['title'] }}</h2>
                    <p class="text-lg text-[#8f5d54] mb-4 leading-relaxed text-left" style="font-size: 13px;">{{ $section['description1'] }}</p>
                    <p class="text-lg text-[#8f5d54] mb-6 leading-relaxed text-left" style="font-size: 13px;">{{ $section['description2'] }}</p>
                    <a href="{{ $section['linkPath'] }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-[#8f5d54] hover:bg-[#8f5d54]/90 transition-colors duration-300 self-center lg:self-start">
                       LEARN MORE
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>