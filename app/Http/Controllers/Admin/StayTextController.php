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
            'title' => 'nullable|string|max:255|unique:stay_texts,label',
            'content' => 'nullable|string',
        ]);

        $text = StayText::create([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json([
            'message' => 'Text content added successfully.',
            'text' => $text->fresh(),
        ]);
    }

    public function update(Request $request, StayText $StayText)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:stay_texts,label,' . $StayText->id,
            'content' => 'nullable|string',
        ]);

        $StayText->update([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json([
            'message' => 'Text content updated successfully.',
            'text' => $StayText->fresh(),
        ]);
    }

    public function destroy(Request $request, StayText $StayText)
    {
        $StayText->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Text content deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}
