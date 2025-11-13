<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FarmInfoText;

class FarmInfoTextController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'farm_info_id' => 'required|exists:farm_infos,id',
            'section' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $text = FarmInfoText::create($request->only(['farm_info_id', 'section', 'title', 'content']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Text section added!',
                'text' => $text
            ]);
        }

        return back()->with('success', 'Text section added!');
    }

    public function update(Request $request, FarmInfoText $farmInfoText)
    {
        $request->validate([
            'section' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $farmInfoText->update($request->only(['section', 'title', 'content']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Text section updated!',
                'text' => $farmInfoText
            ]);
        }

        return back()->with('success', 'Text section updated!');
    }


    public function destroy($id)
    {
        $farmInfoText = FarmInfoText::findOrFail($id);
        $farmInfoText->delete();

        return back()->with('success', 'Text deleted successfully!');
    }
}
