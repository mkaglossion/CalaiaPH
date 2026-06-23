<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecialText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'title' => 'nullable|string|max:255|unique:special_texts,label',
            'content' => 'nullable|string',
        ]);

        SpecialText::create([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json();
    }

    public function update(Request $request, SpecialText $SpecialText)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:special_texts,label,' . $SpecialText->id,
            'content' => 'nullable|string',
        ]);

        $SpecialText->update([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json();
    }

    public function destroy(SpecialText $SpecialText)
    {
        $SpecialText->delete();

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }

    public function saveCopy(Request $request)
    {
        $validated = $request->validate([
            'page_title' => 'nullable|string|max:255',
            'page_intro' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            SpecialText::updateOrCreate(
                ['label' => 'special_events_title'],
                ['text' => $validated['page_title'] ?? '']
            );

            SpecialText::updateOrCreate(
                ['label' => 'special_events_intro'],
                ['text' => $validated['page_intro'] ?? '']
            );
        });

        return response()->json([
            'message' => 'Special events page copy saved successfully.',
        ]);
    }
}
