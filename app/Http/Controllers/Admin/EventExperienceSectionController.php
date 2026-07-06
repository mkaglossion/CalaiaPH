<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventExperienceSection;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class EventExperienceSectionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'section_id' => 'required|string|max:255|unique:event_experience_sections,section_id',
            'title' => 'required|string|max:255',
            'image_path' => 'nullable|string|max:255',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'description1' => 'required|string',
            'description2' => 'required|string',
            'link_path' => 'required|string|max:255',
            'theme' => 'required|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean'
        ]);

        $imagePath = $request->image_path ?: '';

        // Handle file upload to Cloudinary
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
            $imagePath = $uploadResult['secure_url'];
        }

        EventExperienceSection::create([
            'section_id' => $request->section_id,
            'title' => $request->title,
            'image_path' => $imagePath,
            'description1' => $request->description1,
            'description2' => $request->description2,
            'link_path' => $request->link_path,
            'theme' => $request->theme,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true
        ]);

        return response()->json(['message' => 'Section added successfully.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $eventExperienceSection = EventExperienceSection::findOrFail($id);
        
        $request->validate([
            'section_id' => 'required|string|max:255|unique:event_experience_sections,section_id,' . $eventExperienceSection->id,
            'title' => 'required|string|max:255',
            'image_path' => 'nullable|string|max:255',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'remove_image' => 'nullable|in:1',
            'description1' => 'required|string',
            'description2' => 'required|string',
            'link_path' => 'required|string|max:255',
            'theme' => 'required|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean'
        ]);

        $data = [
            'section_id' => $request->section_id,
            'title' => $request->title,
            'description1' => $request->description1,
            'description2' => $request->description2,
            'link_path' => $request->link_path,
            'theme' => $request->theme,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true
        ];

        // Handle image operations
        if ($request->remove_image) {
            $data['image_path'] = '';
        } elseif ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filePath = $file->getRealPath();
            $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
            $data['image_path'] = $uploadResult['secure_url'];
        } else {
            $data['image_path'] = $request->image_path ?: $eventExperienceSection->image_path;
        }

        $eventExperienceSection->update($data);

        return response()->json(['message' => 'Section updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $eventExperienceSection = EventExperienceSection::find($id);

        if ($eventExperienceSection) {
            $eventExperienceSection->delete();
        }

        return response()->json(['message' => 'Section deleted successfully.']);
    }
}
