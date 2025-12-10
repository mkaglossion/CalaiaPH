<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Show the testimonial submission form
     */
    public function create()
    {
        return view('testimonials.create');
    }

    /**
     * Store a newly created testimonial
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5', // Allow 1-5 star ratings
        ]);

        $testimonial = Testimonial::create([
            'name' => $validated['name'],
            'position' => $validated['position'],
            'content' => $validated['content'],
            'rating' => $validated['rating'],
            'status' => 'pending',
            'order' => 0,
        ]);

        return redirect()->route('home')->with('success', 'Thank you for your testimonial! It will be reviewed by our team before being published.');
    }
}