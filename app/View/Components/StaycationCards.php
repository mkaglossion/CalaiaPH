<?php

namespace App\View\Components;

use Illuminate\View\Component;

class StaycationCards extends Component
{
    public $accommodations;

    /**
     * Create a new component instance.
     */
    public function __construct($accommodations = [])
    {
        $this->accommodations = $accommodations;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.staycation-cards');
    }
}