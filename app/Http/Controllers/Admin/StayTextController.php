<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StayText;
use Illuminate\Http\Request;

class StayTextController extends Controller
{
    public function index()
    {
        $texts = StayText::latest()->get();
        return view('admin.stay_texts', compact('texts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255|unique:stay_texts,label',
            'text' => 'nullable|string',
        ]);

        StayText::create($request->only(['label', 'text']));

        return response()->json(['message' => 'Text content added successfully.']);
    }

    public function update(Request $request, StayText $StayText)
    {
        $request->validate([
            'label' => 'required|string|max:255|unique:stay_texts,label,' . $StayText->id,
            'text' => 'nullable|string',
        ]);

        $StayText->update($request->only(['label', 'text']));

        return response()->json(['message' => 'Text content updated successfully.']);
    }

    public function destroy(StayText $StayText)
    {
        $StayText->delete();

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}

