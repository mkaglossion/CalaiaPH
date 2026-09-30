<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Facades\Cloudinary;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::latest()->get();
        return view('admin.events', compact('events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'features' => 'required|string',
            'image' => 'nullable|string|max:2048',
            'public_id' => 'nullable|string|max:255',
        ]);

        $event = new Event();
        $event->forceFill($validated);
        $event->save();
        $event->refresh();

        return response()->json([
            'message' => 'Event added successfully.',
            'event' => $event,
        ]);

    }

    // public function show(Event $event)
    // {
    //     $event->load(['swiperImages', 'texts']);
    //     return view('admin.activities', compact('event'));
    // }

    public function update(Request $request, Event $event_list)
    {
        $oldPublicId = $event_list->public_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'features' => 'required|string',
            'image' => 'nullable|string|max:2048',
            'public_id' => 'nullable|string|max:255',
        ]);

        $event_list->forceFill($validated);
        $event_list->save();
        $event_list->refresh();

        if (!empty($request->input('public_id')) && !empty($oldPublicId) && $request->input('public_id') !== $oldPublicId) {
            try {
                Cloudinary::uploadApi()->destroy($oldPublicId);
            } catch (\Exception $e) {
                // Keep the update successful even if cleanup fails.
            }
        }

        return response()->json([
            'message' => 'Event updated successfully.',
            'event' => $event_list,
        ]);
    }

    public function destroy(Event $event_list)
    {
        if (!empty($event_list->public_id)) {
            try {
                Cloudinary::uploadApi()->destroy($event_list->public_id);
            } catch (\Exception $e) {
                // Keep the delete successful even if cleanup fails.
            }
        }

        $event_list->delete();
        return response()->json(['message' => 'Event deleted successfully.']);
    }
}
