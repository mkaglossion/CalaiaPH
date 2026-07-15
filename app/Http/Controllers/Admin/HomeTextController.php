<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeText;
use Illuminate\Http\Request;

class HomeTextController extends Controller
{
    public function index()
    {
        $texts = HomeText::latest()->get();
        return view('admin.home_texts', compact('texts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:home_texts,label',
            'content' => 'nullable|string',
        ]);

        $text = HomeText::create([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json([
            'message' => 'Text content added successfully.',
            'text' => $text->fresh(),
        ]);
    }

    public function update(Request $request, HomeText $HomeText)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:home_texts,label,' . $HomeText->id,
            'content' => 'nullable|string',
        ]);

        $HomeText->update([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json([
            'message' => 'Text content updated successfully.',
            'text' => $HomeText->fresh(),
        ]);
    }

    public function destroy(Request $request, HomeText $HomeText)
    {
        $HomeText->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Text content deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}
