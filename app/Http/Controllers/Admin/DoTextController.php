<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoText;
use Illuminate\Http\Request;

class DoTextController extends Controller
{
    public function index()
    {
        $texts = DoText::latest()->get();
        return view('admin.do_texts', compact('texts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255|unique:do_texts,label',
            'text' => 'nullable|string',
        ]);

        DoText::create($request->only(['label', 'text']));

        return response()->json(['message' => 'Text content added successfully.']);
    }

    public function update(Request $request, DoText $DoText)
    {
        $request->validate([
            'label' => 'required|string|max:255|unique:do_texts,label,' . $DoText->id,
            'text' => 'nullable|string',
        ]);

        $DoText->update($request->only(['label', 'text']));

        return response()->json(['message' => 'Text content updated successfully.']);
    }

    public function destroy(DoText $DoText)
    {
        $DoText->delete();

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}

