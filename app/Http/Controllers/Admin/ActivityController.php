<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::latest()->get();
        return view('admin.activities', compact('activities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:activities,name',
        ]);

        Activity::create($request->only('name'));

        return response()->json(['message' => 'Activity added successfully.']);

    }

    // public function show(Activity $activity)
    // {
    //     $activity->load(['swiperImages', 'texts']);
    //     return view('admin.activities', compact('activity'));
    // }

    public function update(Request $request, Activity $activity_list)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:activities,name,' . $activity_list->id,
        ]);

        $activity_list->update($request->only('name'));

        return response()->json(['message' => 'Activity updated successfully.']);
    }

    public function destroy(Activity $activity_list)
    {
        $activity_list->delete();
        return redirect()->back()->with('success', 'Activity deleted successfully!');
    }
}
