<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\EventSection;

class EventExperience extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        $sections = EventSection::orderBy('order')->get();
        return view('components.event-experience', compact('sections'));
    }
}