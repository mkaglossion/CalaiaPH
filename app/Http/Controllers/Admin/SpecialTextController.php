<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecialText;
use Illuminate\Http\Request;

class SpecialTextController extends Controller
{
    public function index()
    {
        $texts = SpecialText::latest()->get();
        return view('admin.special_texts', compact('texts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255|unique:special_texts,label',
            'text' => 'nullable|string',
        ]);

        SpecialText::create($request->only(['label', 'text']));

        return response()->json(['message' => 'Text content added successfully.']);
    }

    public function update(Request $request, SpecialText $SpecialText)
    {
        $request->validate([
            'label' => 'required|string|max:255|unique:special_texts,label,' . $SpecialText->id,
            'text' => 'nullable|string',
        ]);

        $SpecialText->update($request->only(['label', 'text']));

        return response()->json(['message' => 'Text content updated successfully.']);
    }

    public function destroy(SpecialText $SpecialText)
    {
        $SpecialText->delete();

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}

