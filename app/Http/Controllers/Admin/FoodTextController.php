<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoodText;
use Illuminate\Http\Request;

class FoodTextController extends Controller
{
    public function index()
    {
        $texts = FoodText::latest()->get();
        return view('admin.food_texts', compact('texts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255|unique:day_tour_texts,label',
            'text' => 'nullable|string',
        ]);

        FoodText::create($request->only(['label', 'text']));

        return response()->json(['message' => 'Text content added successfully.']);
    }

    public function update(Request $request, FoodText $FoodText)
    {
        $request->validate([
            'label' => 'required|string|max:255|unique:day_tour_texts,label,' . $FoodText->id,
            'text' => 'nullable|string',
        ]);

        $FoodText->update($request->only(['label', 'text']));

        return response()->json(['message' => 'Text content updated successfully.']);
    }

    public function destroy(FoodText $FoodText)
    {
        $FoodText->delete();

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}

