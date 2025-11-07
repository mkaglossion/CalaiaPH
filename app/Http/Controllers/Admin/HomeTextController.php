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
            'label' => 'required|string|max:255|unique:home_texts,label',
            'text' => 'nullable|string',
        ]);

        HomeText::create($request->only(['label', 'text']));

        return response()->json(['message' => 'Text content added successfully.']);
    }

    public function update(Request $request, HomeText $HomeText)
    {
        $request->validate([
            'label' => 'required|string|max:255|unique:home_texts,label,' . $HomeText->id,
            'text' => 'nullable|string',
        ]);

        $HomeText->update($request->only(['label', 'text']));

        return response()->json(['message' => 'Text content updated successfully.']);
    }

    public function destroy(HomeText $HomeText)
    {
        $HomeText->delete();

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}

