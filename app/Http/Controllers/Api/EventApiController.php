<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Facades\Cloudinary;

class EventApiController extends Controller
{
    /**
     * Upload image to Cloudinary
     */
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:102400', // max 100MB
            ]);

            if (!$request->hasFile('image')) {
                return response()->json(['success' => false, 'message' => 'No image provided'], 400);
            }

            $file = $request->file('image');
            $filePath = $file->getRealPath();

            try {
                $uploadResult = Cloudinary::uploadApi()->upload($filePath, [
                    'folder' => config('cloudinary.upload.folder') ?: 'special_events',
                    'resource_type' => 'auto',
                ]);
                return response()->json([
                    'success' => true,
                    'imageUrl' => $uploadResult['secure_url'],
                    'public_id' => $uploadResult['public_id'],
                    'message' => 'Image uploaded successfully'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error uploading image: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'features' => 'required|string',
                'image' => 'nullable|string|max:2048',
                'public_id' => 'nullable|string|max:255',
            ]);

            $event = Event::create($validated)->fresh();

            return response()->json([
                'message' => 'Event created successfully',
                'event' => $event
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Update an event
     */
    public function update(Request $request, Event $event)
    {
        try {
            $oldPublicId = $event->public_id;

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'features' => 'required|string',
                'image' => 'nullable|string|max:2048',
                'public_id' => 'nullable|string|max:255',
            ]);

            $event->update($validated);
            $event->refresh();

            if (!empty($validated['public_id']) && !empty($oldPublicId) && $validated['public_id'] !== $oldPublicId) {
                try {
                    Cloudinary::uploadApi()->destroy($oldPublicId);
                } catch (\Exception $e) {
                    // Keep the event update successful even if cleanup fails.
                }
            }

            return response()->json([
                'message' => 'Event updated successfully',
                'event' => $event
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Delete an event
     */
    public function destroy(Event $event)
    {
        // Delete image from Cloudinary if public_id exists
        if ($event->public_id ?? false) {
            try {
                Cloudinary::uploadApi()->destroy($event->public_id);
            } catch (\Exception $e) {
                // Continue even if deletion fails
            }
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }
}
