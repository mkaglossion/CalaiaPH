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

        $text = SpecialText::create([
            'label' => $request->title,
            'text' => $request->content,
        ]);

        return response()->json([
            'message' => 'Text content added successfully.',
            'text' => $text->fresh(),
        ]);
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

        return response()->json([
            'message' => 'Text content updated successfully.',
            'text' => $SpecialText->fresh(),
        ]);
    }

    public function destroy(Request $request, SpecialText $SpecialText)
    {
        $SpecialText->delete();

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
            'cta_primary_label' => 'nullable|string|max:255',
            'cta_primary_url' => 'nullable|string|max:2048',
            'cta_secondary_label' => 'nullable|string|max:255',
            'cta_secondary_url' => 'nullable|string|max:2048',
        ]);

        DB::transaction(function () use ($validated) {
            $fields = [
                'special_events_title' => $validated['page_title'] ?? '',
                'special_events_intro' => $validated['page_intro'] ?? '',
                'special_events_cta_title' => $validated['cta_title'] ?? '',
                'special_events_cta_intro' => $validated['cta_intro'] ?? '',
                'special_events_cta_primary_label' => $validated['cta_primary_label'] ?? '',
                'special_events_cta_primary_url' => $validated['cta_primary_url'] ?? '',
                'special_events_cta_secondary_label' => $validated['cta_secondary_label'] ?? '',
                'special_events_cta_secondary_url' => $validated['cta_secondary_url'] ?? '',
            ];

            foreach ($fields as $label => $text) {
                SpecialText::updateOrCreate(
                    ['label' => $label],
                    ['text' => $text]
                );
            }
        });

        return response()->json([
            'message' => 'Special events page copy saved successfully.',
        ]);
    }
}
