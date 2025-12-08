<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccommodationText;
use Illuminate\Http\Request;

class AccommodationTextController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'accommodation_id' => 'required|exists:staycation_accommodations,id',
            'section' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $text = AccommodationText::create($request->only(['accommodation_id', 'section', 'title', 'content']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Text section added!',
                'text' => $text
            ]);
        }

        return back()->with('success', 'Text section added!');
    }

    public function update(Request $request, AccommodationText $accommodationText)
    {
        $request->validate([
            'section' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $accommodationText->update($request->only(['section', 'title', 'content']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Text section updated!',
                'text' => $accommodationText
            ]);
        }

        return back()->with('success', 'Text section updated!');
    }

    public function destroy($id)
    {
        $accommodationText = AccommodationText::findOrFail($id);
        $accommodationText->delete();

        return back()->with('success', 'Text deleted successfully!');
    }
}



