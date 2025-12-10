<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the testimonials
     */
    public function index()
    {
        $testimonials = Testimonial::orderBy('status')
            ->orderBy('order')
            ->get();

        return view('admin.testimonials.index', compact('testimonials'));
    }

    /**
     * Approve a testimonial
     */
    public function approve(Testimonial $testimonial)
    {
        $testimonial->update(['status' => 'approved']);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial approved successfully!');
    }

    /**
     * Update the order of testimonials
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'testimonials' => 'required|array',
            'testimonials.*.id' => 'required|exists:testimonials,id',
            'testimonials.*.order' => 'required|integer',
        ]);

        foreach ($request->testimonials as $testimonialData) {
            Testimonial::where('id', $testimonialData['id'])
                ->update(['order' => $testimonialData['order']]);
        }

        return response()->json(['message' => 'Order updated successfully']);
    }

    /**
     * Remove the specified testimonial
     */
    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully!');
    }
}