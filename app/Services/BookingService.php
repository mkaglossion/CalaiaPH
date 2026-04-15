<?php

namespace App\Services;

use App\Models\Activity;

class BookingService
{
    public function getActivitiesData()
    {
        // Fetch activities dynamically from database where is_active = true with their images
        $activities = Activity::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($activity) {
                // Handle both Cloudinary URLs and local storage paths
                $imageUrl = null;
                if ($activity->image) {
                    // Check if it's already a full URL (Cloudinary)
                    if (filter_var($activity->image, FILTER_VALIDATE_URL)) {
                        $imageUrl = $activity->image;
                    } else {
                        // If it's a local path, convert to full storage URL
                        $imageUrl = url('storage/' . $activity->image);
                    }
                }

                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'description' => $activity->description,
                    'duration' => $activity->duration,
                    'price' => (float) $activity->price,
                    'availability' => $activity->availability ?? 'Available',
                    'image' => $imageUrl,
                    'type' => 'predefined'
                ];
            })
            ->values()
            ->toArray();

        // Fallback to empty array if no activities found
        return $activities ?: [];
    }

    public function saveActivities($activities, $saveKey)
    {
        // Save activities to session
        session([$saveKey => $activities]);
    }
}