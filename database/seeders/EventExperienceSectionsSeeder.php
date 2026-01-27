<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EventExperienceSection;

class EventExperienceSectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                "section_id" => "staycation",
                "title" => "WHERE TO STAY.",
                "image_path" => "/assets/images/carousel/wheretostay.jpg",
                "description1" => "Hotel-style comfort in the great outdoors—cozy beds, stylish tents, and thoughtful amenities.",
                "description2" => "Glamping, Tent Rental, BYOT, or Car Camping—four ways to stay close to nature, from plush comfort to DIY simplicity, with shared facilities and scenic spots throughout.",
                "link_path" => "/staycation",
                "theme" => "beige_swatch",
                "order" => 1,
                "is_active" => true,
            ],
            [
                "section_id" => "activities",
                "title" => "WHAT TO DO.",
                "image_path" => "/assets/images/carousel/whattodo.jpg",
                "description1" => "Showcases all the fun and enriching activities guests can enjoy during their stay. This includes nature-based experiences, adventure rides, creative crafts, farm visits and indoor/outdoor games — each paired with a photo and description.",
                "description2" => "From slow nature walks to splashy adventures, Calaia packs your day with things to do. Choose from guided trails, farm encounters, crafty workshops, and friendly games—each with a photo, quick brief, and what to expect.",
                "link_path" => "/activities",
                "theme" => "white",
                "order" => 2,
                "is_active" => true,
            ],
            [
                "section_id" => "fnb",
                "title" => "FOOD AND BEVERAGE.",
                "image_path" => "/assets/images/carousel/f&b.jpg",
                "description1" => "From freshly-brewed coffee to grilled meats, we have all your meals covered for a worry-free stay.",
                "description2" => "Enjoy casual dining areas, picnic-ready options, and barista-made coffee—perfect for slow mornings and golden-hour sunsets. Ask our team for today's specials and seasonal picks.",
                "link_path" => "/dining",
                "theme" => "tan",
                "order" => 3,
                "is_active" => true,
            ],
            [
                "section_id" => "groups",
                "title" => "GROUPS.",
                "image_path" => "/assets/images/carousel/groups.jpg",
                "description1" => "Strong and effective teams are built with purpose.",
                "description2" => "Calaia Nature Park is the perfect partner for your next team building event. Our packages include comfortable stays, nourishing meals, and group activities to create stronger bonds.",
                "link_path" => "/groups",
                "theme" => "white",
                "order" => 4,
                "is_active" => true,
            ],
            [
                "section_id" => "farm",
                "title" => "OUR FARM.",
                "image_path" => "/assets/images/carousel/farm.jpg",
                "description1" => "Take a leisurely stroll around our farm where we diligently practice natural methods of farming.",
                "description2" => "Taking wisdom from permaculture practices, we do not use chemical pesticides or herbicides. Our farm animals also share their expertise in pest control and fertilizer production.",
                "link_path" => "/farm",
                "theme" => "beige_swatch",
                "order" => 5,
                "is_active" => true,
            ],
        ];

        foreach ($sections as $section) {
            EventExperienceSection::create($section);
        }
    }
}
