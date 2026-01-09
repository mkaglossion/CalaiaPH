{{-- event-experience.blade.php --}}
@php
    $sections = [
        [
            "id" => "staycation",
            "title" => "WHERE TO STAY.",
            "imagePath" => "/assets/images/carousel/wheretostay.jpg",
            "description1" => "Hotel-style comfort in the great outdoors—cozy beds, stylish tents, and thoughtful amenities.",
            "description2" => "Glamping, Tent Rental, BYOT, or Car Camping—four ways to stay close to nature, from plush comfort to DIY simplicity, with shared facilities and scenic spots throughout.",
            "linkPath" => "/staycation",
            "theme" => "beige_swatch", // ✅ swatch bg
        ],
        [
            "id" => "activities",
            "title" => "WHAT TO DO.",
            "imagePath" => "/assets/images/carousel/whattodo.jpg",
            "description1" => "Showcases all the fun and enriching activities guests can enjoy during their stay. This includes nature-based experiences, adventure rides, creative crafts, farm visits and indoor/outdoor games — each paired with a photo and description.",
            "description2" => "From slow nature walks to splashy adventures, Calaia packs your day with things to do. Choose from guided trails, farm encounters, crafty workshops, and friendly games—each with a photo, quick brief, and what to expect.",
            "linkPath" => "/activities",
            "theme" => "white",
        ],
        [
            "id" => "fnb",
            "title" => "FOOD AND BEVERAGE.",
            "imagePath" => "/assets/images/carousel/f&b.jpg",
            "description1" => "From freshly-brewed coffee to grilled meats, we have all your meals covered for a worry-free stay.",
            "description2" => "Enjoy casual dining areas, picnic-ready options, and barista-made coffee—perfect for slow mornings and golden-hour sunsets. Ask our team for today's specials and seasonal picks.",
            "linkPath" => "/dining",
            "theme" => "tan",
        ],
        [
            "id" => "groups",
            "title" => "GROUPS.",
            "imagePath" => "/assets/images/carousel/groups.jpg",
            "description1" => "Strong and effective teams are built with purpose.",
            "description2" => "Calaia Nature Park is the perfect partner for your next team building event. Our packages include comfortable stays, nourishing meals, and group activities to create stronger bonds.",
            "linkPath" => "/groups",
            "theme" => "white",
        ],
        [
            "id" => "farm",
            "title" => "OUR FARM.",
            "imagePath" => "/assets/images/carousel/farm.jpg",
            "description1" => "Take a leisurely stroll around our farm where we diligently practice natural methods of farming.",
            "description2" => "Taking wisdom from permaculture practices, we do not use chemical pesticides or herbicides. Our farm animals also share their expertise in pest control and fertilizer production.",
            "linkPath" => "/farm",
            "theme" => "beige_swatch", // ✅ swatch bg
        ],
    ];

    $theme = [
        "beige_swatch" => [
            "bg" => "bg-[#EADAC3]",   // ✅ exact swatch
            "title" => "text-[#8b5a52]",
            "text" => "text-[#393a35]",
            "btn" => "bg-[#b0775c] text-white hover:bg-[#9d6a50]",
        ],
        "white" => [
            "bg" => "bg-white",
            "title" => "text-[#8b5a52]",
            "text" => "text-[#393a35]",
            "btn" => "bg-[#b0775c] text-white hover:bg-[#9d6a50]",
        ],
        "tan" => [
            "bg" => "bg-[#b57c63]",
            "title" => "text-white",
            "text" => "text-white/90",
            "btn" => "bg-[#566057] text-white hover:bg-[#4b4b4b]",
        ],
    ];
@endphp

<section class="w-full">
    @foreach ($sections as $i => $section)
        @php
            $t = $theme[$section["theme"]] ?? $theme["white"];
            $reverse = $i % 2 === 1; // alternate left/right on desktop
        @endphp

        <div id="{{ $section['id'] }}" class="{{ $t['bg'] }} py-14 md:py-16">
            <div class="mx-auto max-w-6xl px-6 md:px-10">
                {{-- ✅ Responsive:
                     Mobile: stacked
                     Desktop (md+): 2 columns w/ alternating order
                --}}
                <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-10 md:gap-14">
                    {{-- IMAGE --}}
                    <div class="{{ $reverse ? 'md:order-2' : 'md:order-1' }}">
                        <img
                            src="{{ asset($section['imagePath']) }}"
                            alt="{{ $section['title'] }}"
                            class="w-full h-80 object-cover rounded-lg shadow-lg"
                        >
                    </div>

                    {{-- TEXT --}}
                    <div class="{{ $reverse ? 'md:order-1' : 'md:order-2' }}">
                        <h2
                            class="uppercase font-bold tracking-wide {{ $t['title'] }} mb-4 text-center md:text-left"
                            style="font-family:'Gotham Book', sans-serif; font-size:25px;"
                        >
                            {{ $section['title'] }}
                        </h2>

                        <div class="max-w-[520px] mx-auto md:mx-0">
                            <p
                                class="{{ $t['text'] }} text-[13px] leading-[1.7] mb-3 text-center md:text-left"
                                style="font-family:'Gotham Book', sans-serif;"
                            >
                                {{ $section['description1'] }}
                            </p>

                            <p
                                class="{{ $t['text'] }} text-[13px] leading-[1.7] mb-6 text-center md:text-left"
                                style="font-family:'Gotham Book', sans-serif;"
                            >
                                {{ $section['description2'] }}
                            </p>

                            <a
                                href="{{ $section['linkPath'] }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full transition-colors duration-300 self-center lg:self-start {{ $t['btn'] }}"
                                style="font-family:'Gotham Book', sans-serif;"
                            >
                                LEARN MORE
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</section>