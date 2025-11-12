<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventText;

class EventTextController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'section' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $text = EventText::create($request->only(['event_id', 'section', 'title', 'content']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Text section added!',
                'text' => $text
            ]);
        }

        return back()->with('success', 'Text section added!');
    }

    public function update(Request $request, EventText $eventText)
    {
        $request->validate([
            'section' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $eventText->update($request->only(['section', 'title', 'content']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Text section updated!',
                'text' => $eventText
            ]);
        }

        return back()->with('success', 'Text section updated!');
    }


    public function destroy(EventText $eventText)
    {
        $eventText->delete();
        return back()->with('success', 'Text deleted successfully!');
    }
}
