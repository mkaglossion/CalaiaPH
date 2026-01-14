<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('activities', 'public');
        }

        Activity::create([
            'name' => $request->name,
            'description' => $request->description,
            'duration' => $request->duration ?? '',
            'price' => $request->price ?? 0,
            'availability' => $request->availability ?? 'Available',
            'image' => $imagePath,
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
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'duration' => $request->duration ?? '',
            'price' => $request->price ?? $activity_list->price,
            'availability' => $request->availability ?? $activity_list->availability,
            'category' => $request->category ?? $activity_list->category,
            'is_active' => $request->is_active ?? $activity_list->is_active,
            'sort_order' => $request->sort_order ?? $activity_list->sort_order,
            'active' => $request->active ?? $activity_list->active,
        ];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($activity_list->image && Storage::disk('public')->exists($activity_list->image)) {
                Storage::disk('public')->delete($activity_list->image);
            }

            // Store new image
            $data['image'] = $request->file('image')->store('activities', 'public');
        }

        $activity_list->update($data);

        return response()->json(['message' => 'Activity updated successfully.']);
    }

    public function destroy(Activity $activity_list)
    {
        // Delete associated image if exists
        if ($activity_list->image && Storage::disk('public')->exists($activity_list->image)) {
            Storage::disk('public')->delete($activity_list->image);
        }

        $activity_list->delete();
        return redirect()->back()->with('success', 'Activity deleted successfully!');
    }
}
