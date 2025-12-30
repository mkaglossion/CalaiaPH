<?php

namespace App\Http\Controllers;

use App\Models\DayTourSwiperImage;
use App\Models\DayTourText;
use App\Models\DayTour;
use App\Models\Activity;
use Illuminate\Http\Request;

class DayTourController extends Controller
{
    public function index()
    {
        if (request()->is('admin/*')) {
            $tours = DayTour::with('activities')->get();
            return view('admin.days', compact('tours'));
        } else {
            $swiperImages = DayTourSwiperImage::all();
            $texts = DayTourText::whereNotNull('title')->pluck('content', 'title')->mapWithKeys(function ($content, $title) {
                return [strtolower($title) => $content];
            })->toArray();

            $packages = config('daytour.packages');

            return view('daytour', compact('packages', 'swiperImages', 'texts'));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:day_tours,name',
        ]);

        $tour = DayTour::create($request->only(['name', 'description', 'price']));
        $tour->activities()->sync($request->activity_ids);

        // If the request is AJAX, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Day Tour created!',
                'tour' => $tour->load('activities')
            ]);
        }

        // fallback for normal form submit
        return back()->with('success', 'Day Tour created!');
    }

    public function update(Request $request, DayTour $dayTour)
    {
        $dayTour->update($request->only(['name', 'description', 'price']));

        $dayTour->activities()->sync($request->activity_ids);

        return back()->with('success', 'Day Tour updated!');
    }

    public function destroy(DayTour $dayTour)
    {
        $dayTour->delete();
        return back()->with('success', 'Day Tour deleted!');
    }
}