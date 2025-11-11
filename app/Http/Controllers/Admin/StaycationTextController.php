<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaycationText;

class StaycationTextController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'staycation_id' => 'required|exists:staycations,id',
            'section' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $text = StaycationText::create($request->only(['staycation_id', 'section', 'title', 'content']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Text section added!',
                'text' => $text
            ]);
        }

        return back()->with('success', 'Text section added!');
    }

    public function update(Request $request, StaycationText $staycationText)
    {
        $request->validate([
            'section' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $staycationText->update($request->only(['section', 'title', 'content']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Text section updated!',
                'text' => $staycationText
            ]);
        }

        return back()->with('success', 'Text section updated!');
    }


    public function destroy(StaycationText $staycationText)
    {
        $staycationText->delete();
        return back()->with('success', 'Text deleted successfully!');
    }
}
