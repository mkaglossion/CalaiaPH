<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::latest()->paginate(10);
        return view('admin.whatToDo.activities', compact('activities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:activities,name',
            'description' => 'nullable|string',
            'duration' => 'nullable|string',
        ]);

        Activity::create([
            'name' => $request->name,
            'description' => $request->description,
            'duration' => $request->duration ?? '',
            'price' => $request->price ?? 0,
            'availability' => $request->availability ?? 'Available',
            'image' => $request->image ?? '',
            'category' => $request->category ?? '',
            'is_active' => $request->is_active ?? 1,
            'sort_order' => $request->sort_order ?? 0,
            'active' => $request->active ?? 1,
        ]);

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
            'description' => 'nullable|string',
            'duration' => 'nullable|string',
        ]);

        $activity_list->update([
            'name' => $request->name,
            'description' => $request->description,
            'duration' => $request->duration ?? '',
            'price' => $request->price ?? $activity_list->price,
            'availability' => $request->availability ?? $activity_list->availability,
            'image' => $request->image ?? $activity_list->image,
            'category' => $request->category ?? $activity_list->category,
            'is_active' => $request->is_active ?? $activity_list->is_active,
            'sort_order' => $request->sort_order ?? $activity_list->sort_order,
            'active' => $request->active ?? $activity_list->active,
        ]);

        return response()->json(['message' => 'Activity updated successfully.']);
    }

    public function destroy(Activity $activity_list)
    {
        $activity_list->delete();
        return redirect()->back()->with('success', 'Activity deleted successfully!');
    }
}
