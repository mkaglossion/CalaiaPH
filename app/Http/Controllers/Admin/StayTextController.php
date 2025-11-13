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
            'label' => 'nullable|string|max:255|unique:stay_texts,label',
            'text' => 'nullable|string',
        ]);

        StayText::create($request->only(['label', 'text']));

        return response()->json();
    }

    public function update(Request $request, StayText $StayText)
    {
        $request->validate([
            'label' => 'nullable|string|max:255|unique:stay_texts,label,' . $StayText->id,
            'text' => 'nullable|string',
        ]);

        $StayText->update($request->only(['label', 'text']));

        return response()->json();
    }

    public function destroy(StayText $StayText)
    {
        $StayText->delete();

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}
