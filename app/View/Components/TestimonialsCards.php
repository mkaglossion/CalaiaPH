<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Testimonial;

class TestimonialsCards extends Component
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
        $testimonials = Testimonial::where('status', 'approved')
            ->orderBy('order')
            ->get();

        return view('components.testimonials-cards', compact('testimonials'));
    }
}
