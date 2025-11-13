<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $request->validate([
            'name' => 'required|string|max:255|unique:events,name',
        ]);

        Event::create($request->only('name'));

        return response()->json(['message' => 'Event added successfully.']);

    }

    // public function show(Event $event)
    // {
    //     $event->load(['swiperImages', 'texts']);
    //     return view('admin.activities', compact('event'));
    // }

    public function update(Request $request, Event $event_list)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:events,name,' . $event_list->id,
        ]);

        $event_list->update($request->only('name'));

        return response()->json(['message' => 'Event updated successfully.']);
    }

    public function destroy(Event $event_list)
    {
        $event_list->delete();
        return redirect()->back()->with('success', 'Event deleted successfully!');
    }
}
