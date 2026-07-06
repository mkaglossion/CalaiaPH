<?php

namespace App\View\Components;

use App\Models\EventExperienceSection;
use Illuminate\View\Component;

class EventExperience extends Component
{
    public $sections;

    public function __construct()
    {
        // Keep this component strictly database-backed so it does not fall back
        // to local public assets when the CMS already has uploaded Cloudinary URLs.
        $this->sections = EventExperienceSection::where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function render()
    {
        return view('components.event-experience', [
            'sections' => $this->sections,
        ]);
    }
}
