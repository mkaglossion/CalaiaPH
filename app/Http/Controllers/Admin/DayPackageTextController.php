<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DayPackageText;

class DayPackageTextController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:day_tours,id',
            'section' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $text = DayPackageText::create($request->only(['package_id', 'section', 'title', 'content']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Text section added!',
                'text' => $text
            ]);
        }

        return back()->with('success', 'Text section added!');
    }

    public function update(Request $request, DayPackageText $dayPackageText)
    {
        $request->validate([
            'section' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $dayPackageText->update($request->only(['section', 'title', 'content']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Text section updated!',
                'text' => $dayPackageText
            ]);
        }

        return back()->with('success', 'Text section updated!');
    }

    public function destroy($id)
    {
        $dayPackageText = DayPackageText::find($id);

        if ($dayPackageText) {
            $dayPackageText->delete();
        }

        return back()->with('success', 'Text deleted successfully!');
    }
}
