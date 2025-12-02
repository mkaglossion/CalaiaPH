<?php

namespace App\Services;

class BookingService
{
    public function getActivitiesData()
    {
        // Return hardcoded activities data for now
        return [
            [
                'id' => 1,
                'name' => 'Farm Tour',
                'description' => 'Guided tour of the farm facilities',
                'price' => 500,
                'type' => 'predefined'
            ],
            [
                'id' => 2,
                'name' => 'Animal Feeding',
                'description' => 'Help feed the animals',
                'price' => 300,
                'type' => 'predefined'
            ],
            [
                'id' => 3,
                'name' => 'Custom Activity',
                'description' => 'Custom activity request',
                'price' => 0,
                'type' => 'custom'
            ]
        ];
    }

    public function saveActivities($activities, $saveKey)
    {
        // Save activities to session
        session([$saveKey => $activities]);
    }
}