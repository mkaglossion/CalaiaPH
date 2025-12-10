<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventSection;
use Illuminate\Http\Request;

class EventSectionController extends Controller
{
    public function index()
    {
        $sections = EventSection::orderBy('order')->get();
        return response()->json(['success' => true, 'data' => $sections]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|string|unique:event_sections,section_id',
            'title' => 'required|string',
            'image_path' => 'required|string',
            'description1' => 'required|string',
            'description2' => 'required|string',
            'link_path' => 'required|string',
            'order' => 'required|integer'
        ]);

        $section = EventSection::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully',
            'data' => $section
        ]);
    }

    public function update(Request $request, EventSection $event_section)
    {
        $validated = $request->validate([
            'section_id' => 'sometimes|string|unique:event_sections,section_id,'.$event_section->id,
            'title' => 'sometimes|string',
            'image_path' => 'sometimes|string',
            'description1' => 'sometimes|string',
            'description2' => 'sometimes|string',
            'link_path' => 'sometimes|string',
            'order' => 'sometimes|integer'
        ]);

        $event_section->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully',
            'data' => $event_section
        ]);
    }

    public function destroy(EventSection $event_section)
    {
        $event_section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully'
        ]);
    }
}