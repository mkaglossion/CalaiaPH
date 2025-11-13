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
            'label' => 'nullable|string|max:255|unique:day_tour_texts,label',
            'text' => 'nullable|string',
        ]);

        DayTourText::create($request->only(['label', 'text']));

        return response()->json(['message' => 'Text content added successfully.']);
    }

    public function update(Request $request, DayTourText $dayTourText)
    {
        $request->validate([
            'label' => 'nullable|string|max:255|unique:day_tour_texts,label,' . $dayTourText->id,
            'text' => 'nullable|string',
        ]);

        $dayTourText->update($request->only(['label', 'text']));

        return response()->json(['message' => 'Text content updated successfully.']);
    }

    public function destroy(DayTourText $dayTourText)
    {
        $dayTourText->delete();

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}

