<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class EventApiController extends Controller
{
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
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:102400' // max 100MB
            ]);

            // Handle image upload to Cloudinary
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filePath = $file->getRealPath();

                try {
                    $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
                    $validated['image'] = $uploadResult['secure_url'];
                    $validated['public_id'] = $uploadResult['public_id'];
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Image upload failed',
                        'errors' => ['image' => ['Failed to upload image to Cloudinary: ' . $e->getMessage()]]
                    ], 422);
                }
            }

            $event = Event::create($validated);

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
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'features' => 'required|string',
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:102400' // max 100MB
            ]);

            // Handle image upload/replacement
            if ($request->hasFile('image')) {
                // Delete old image from Cloudinary if exists
                if ($event->public_id) {
                    try {
                        Cloudinary::uploadApi()->destroy($event->public_id);
                    } catch (\Exception $e) {
                        // Continue even if deletion fails
                    }
                }

                // Upload new image to Cloudinary
                $file = $request->file('image');
                $filePath = $file->getRealPath();

                try {
                    $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
                    $validated['image'] = $uploadResult['secure_url'];
                    $validated['public_id'] = $uploadResult['public_id'];
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Image upload failed',
                        'errors' => ['image' => ['Failed to upload image to Cloudinary: ' . $e->getMessage()]]
                    ], 422);
                }
            }

            $event->update($validated);

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
