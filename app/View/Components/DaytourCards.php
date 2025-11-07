<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DaytourCards extends Component
{
    public $packages;

    /**
     * Create a new component instance.
     */
    public function __construct($packages = [])
    {
        $this->packages = $packages;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.daytour-cards');
    }
}