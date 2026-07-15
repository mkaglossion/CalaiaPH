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
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $text = FoodText::create([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json([
            'message' => 'Text content added successfully.',
            'text' => $text->fresh(),
        ]);
    }

    public function update(Request $request, FoodText $FoodText)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:food_texts,label,' . $FoodText->id,
            'content' => 'nullable|string',
        ]);

        $FoodText->update([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json([
            'message' => 'Text content updated successfully.',
            'text' => $FoodText->fresh(),
        ]);
    }

    public function destroy(Request $request, FoodText $FoodText)
    {
        $FoodText->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Text content deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}
