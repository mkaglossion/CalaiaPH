<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staycation;
use App\Models\StaycationAccommodation;

class StaycationAccommodationController extends Controller
{
    public function index()
    {
        $accommodations = StaycationAccommodation::with('accommodationVariants')->get();
        return view('admin.accommodation', compact('accommodation'));
    }

   public function store(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'name' => 'required|string|max:255|unique:staycation_accommodations,name',
            'staycation_ids' => 'nullable|array',
            'staycation_ids.*' => 'exists:staycations,id', // make sure IDs exist
        ]);

        // Create the accommodation
        $accommodation = StaycationAccommodation::create($request->only(['name', 'description', 'price']));

        // Sync the related staycations (variants)
        $accommodation->accommodationVariants()->sync($request->staycation_ids ?? []);

        // Return JSON if AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Accommodation created!',
                'accommodation' => $accommodation->load('accommodationVariants'), // eager load variants
            ]);
        }

        // fallback for normal form submit
        return back()->with('success', 'Accommodation created successfully!');
    }


    public function update(Request $request, StaycationAccommodation $dayTour)
    {
        $StaycationAccommodation->update($request->only(['name', 'description', 'price']));

        $StaycationAccommodation->accommodationVariants()->sync($request->staycation_variant_id);

        return back()->with('success', 'Day accommodation updated!');
    }

    public function destroy(StaycationAccommodation $dayTour)
    {
        $StaycationAccommodation->delete();
        return back()->with('success', 'Day accommodation deleted!');
    }
}

