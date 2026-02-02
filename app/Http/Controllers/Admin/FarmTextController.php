<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FarmText;
use Illuminate\Http\Request;

class FarmTextController extends Controller
{
    public function index()
    {
        $texts = FarmText::latest()->get();
        return view('admin.farm_texts', compact('texts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:farm_texts,label',
            'content' => 'nullable|string',
        ]);

        FarmText::create([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json();
    }

    public function update(Request $request, FarmText $FarmText)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:farm_texts,label,' . $FarmText->id,
            'content' => 'nullable|string',
        ]);

        $FarmText->update([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json();
    }

    public function destroy(FarmText $FarmText)
    {
        $FarmText->delete();

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}
