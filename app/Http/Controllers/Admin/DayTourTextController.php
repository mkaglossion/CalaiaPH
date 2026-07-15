<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTourText;
use Illuminate\Http\Request;

class DayTourTextController extends Controller
{
    public function index()
    {
        $texts = DayTourText::latest()->get();
        return view('admin.day_tour_texts', compact('texts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'daytour_id' => 'nullable|exists:day_tours,id',
        ]);

        try {
            // For Important Information section, find or create based on title
            $text = DayTourText::where('title', $request->title)->first();
            if ($text) {
                $text->update($request->only(['content', 'daytour_id']));
                return response()->json([
                    'message' => 'Text content updated successfully.',
                    'text' => $text->fresh(),
                ]);
            } else {
                $text = DayTourText::create($request->only(['title', 'content', 'daytour_id']));
                return response()->json([
                    'message' => 'Text content added successfully.',
                    'text' => $text->fresh(),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to save text: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, DayTourText $dayTourText)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'daytour_id' => 'nullable|exists:day_tours,id',
        ]);

        try {
            $dayTourText->update($request->only(['title', 'content', 'daytour_id']));

            return response()->json([
                'message' => 'Text content updated successfully.',
                'text' => $dayTourText->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update text: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, DayTourText $dayTourText)
    {
        $dayTourText->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Text content deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Text content deleted successfully.');
    }
}
