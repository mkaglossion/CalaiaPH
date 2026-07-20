<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FarmText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $text = FarmText::create([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json([
            'message' => 'Text content added successfully.',
            'text' => $text->fresh(),
        ]);
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

        return response()->json([
            'message' => 'Text content updated successfully.',
            'text' => $FarmText->fresh(),
        ]);
    }

    public function destroy(Request $request, FarmText $FarmText)
    {
        $FarmText->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Text content deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }

    public function saveCopy(Request $request)
    {
        $validated = $request->validate([
            'page_title' => 'nullable|string|max:255',
            'page_intro' => 'nullable|string',
            'cta_title' => 'nullable|string|max:255',
            'cta_intro' => 'nullable|string',
            'cta_button' => 'nullable|string|max:255',
            'cta_note' => 'nullable|string',
        ]);

        $copyMap = [
            'farm_page_title' => $validated['page_title'] ?? '',
            'farm_page_intro' => $validated['page_intro'] ?? '',
            'farm_cta_title' => $validated['cta_title'] ?? '',
            'farm_cta_intro' => $validated['cta_intro'] ?? '',
            'farm_cta_button' => $validated['cta_button'] ?? '',
            'farm_cta_note' => $validated['cta_note'] ?? '',
        ];

        DB::transaction(function () use ($copyMap) {
            foreach ($copyMap as $label => $text) {
                FarmText::updateOrCreate(['label' => $label], ['text' => $text]);
            }
        });

        return response()->json([
            'message' => 'Farm page copy saved successfully.',
        ]);
    }
}
