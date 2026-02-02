<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\EventExperienceSection;

class EventExperience extends Component
{
    public $sections;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        // Force fresh data by bypassing any potential caching
        $this->sections = EventExperienceSection::where('is_active', true)->orderBy('order')->get();

        // Fallback to hardcoded data if no sections in database
        if ($this->sections->isEmpty()) {
            $this->sections = collect([
                (object)[
                    "section_id" => "staycation",
                    "title" => "WHERE TO STAY.",
                    "image_path" => "/assets/images/carousel/wheretostay.jpg",
                    "description1" => "Hotel-style comfort in the great outdoors—cozy beds, stylish tents, and thoughtful amenities.",
                    "description2" => "Glamping, Tent Rental, BYOT, or Car Camping—four ways to stay close to nature, from plush comfort to DIY simplicity, with shared facilities and scenic spots throughout.",
                    "link_path" => "/staycation",
                    "theme" => "beige_swatch",
                ],
                (object)[
                    "section_id" => "activities",
                    "title" => "WHAT TO DO.",
                    "image_path" => "/assets/images/carousel/whattodo.jpg",
                    "description1" => "Showcases all the fun and enriching activities guests can enjoy during their stay. This includes nature-based experiences, adventure rides, creative crafts, farm visits and indoor/outdoor games — each paired with a photo and description.",
                    "description2" => "From slow nature walks to splashy adventures, Calaia packs your day with things to do. Choose from guided trails, farm encounters, crafty workshops, and friendly games—each with a photo, quick brief, and what to expect.",
                    "link_path" => "/activities",
                    "theme" => "white",
                ],
                (object)[
                    "section_id" => "fnb",
                    "title" => "FOOD AND BEVERAGE.",
                    "image_path" => "/assets/images/carousel/f&b.jpg",
                    "description1" => "From freshly-brewed coffee to grilled meats, we have all your meals covered for a worry-free stay.",
                    "description2" => "Enjoy casual dining areas, picnic-ready options, and barista-made coffee—perfect for slow mornings and golden-hour sunsets. Ask our team for today's specials and seasonal picks.",
                    "link_path" => "/dining",
                    "theme" => "tan",
                ],
                (object)[
                    "section_id" => "groups",
                    "title" => "GROUPS.",
                    "image_path" => "/assets/images/carousel/groups.jpg",
                    "description1" => "Strong and effective teams are built with purpose.",
                    "description2" => "Calaia Nature Park is the perfect partner for your next team building event. Our packages include comfortable stays, nourishing meals, and group activities to create stronger bonds.",
                    "link_path" => "/groups",
                    "theme" => "white",
                ],
                (object)[
                    "section_id" => "farm",
                    "title" => "OUR FARM.",
                    "image_path" => "/assets/images/carousel/farm.jpg",
                    "description1" => "Take a leisurely stroll around our farm where we diligently practice natural methods of farming.",
                    "description2" => "Taking wisdom from permaculture practices, we do not use chemical pesticides or herbicides. Our farm animals also share their expertise in pest control and fertilizer production.",
                    "link_path" => "/farm",
                    "theme" => "beige_swatch",
                ],
            ]);
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        $sections = EventExperienceSection::orderBy('order')->get();
        return view('components.event-experience', compact('sections'));
    }
}