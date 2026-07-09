<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityText;

class ActivityTextController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'activity_id' => 'nullable|exists:activities,id',
            'section' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'label' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'text' => 'nullable|string',
        ]);

        // Support both label/text and title/content field names
        $label = $request->input('label') ?? $request->input('title');
        $content = $request->input('text') ?? $request->input('content');

        $text = ActivityText::create([
            'activity_id' => $request->input('activity_id'),
            'section' => $request->input('section'),
            'title' => $label,
            'content' => $content,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Text section added!',
                'text' => $text
            ]);
        }

        return back()->with('success', 'Text section added!');
    }

    public function update(Request $request, ActivityText $activityText)
    {
        $request->validate([
            'section' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'label' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'text' => 'nullable|string',
        ]);

        // Support both label/text and title/content field names
        $label = $request->input('label') ?? $request->input('title');
        $content = $request->input('text') ?? $request->input('content');

        $activityText->update([
            'section' => $request->input('section'),
            'title' => $label,
            'content' => $content,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Text section updated!',
                'text' => $activityText
            ]);
        }

        return back()->with('success', 'Text section updated!');
    }


    public function destroy(ActivityText $activityText)
    {
        $activityText->delete();
        return back()->with('success', 'Text deleted successfully!');
    }
}
