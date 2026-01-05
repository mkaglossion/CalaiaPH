<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTourText;
use Illuminate\Http\Request;

class DayTourTextController extends Controller
{
    public function index()
    {
        $texts = DayTourText::latest()->get();
        return view('admin.day_tour_texts', compact('texts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:day_tour_texts,title',
            'content' => 'nullable|string',
            'daytour_id' => 'nullable|exists:day_tours,id',
        ]);

        try {
            DayTourText::create($request->only(['title', 'content', 'daytour_id']));

            return response()->json(['message' => 'Text content added successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to add text: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, DayTourText $dayTourText)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:day_tour_texts,title,' . $dayTourText->id,
            'content' => 'nullable|string',
            'daytour_id' => 'nullable|exists:day_tours,id',
        ]);

        try {
            $dayTourText->update($request->only(['title', 'content', 'daytour_id']));

            return response()->json(['message' => 'Text content updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update text: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(DayTourText $dayTourText)
    {
        $dayTourText->delete();

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}

