<?php

namespace App\Services;

class ValidationService
{
    /**
     * Get common personal information validation rules
     */
    private static function personalInfoRules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'special_requests' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get common booking validation rules
     */
    public static function bookingRules(int $maxGuests = 50): array
    {
        return array_merge(self::personalInfoRules(), [
            'booking_date' => 'required|date|after:yesterday',
            'guests' => 'required|integer|min:1|max:' . $maxGuests,
            'type' => 'required|string|in:day-visit,room,special-event',
            'package' => 'required|string',
            'price' => 'required|numeric',
        ]);
    }

    /**
     * Get payment validation rules
     */
    public static function paymentRules(): array
    {
        return array_merge(self::personalInfoRules(), [
            'payment_method' => 'required|in:gcash,paymaya,bank',
            'terms' => 'accepted',
        ]);
    }

    /**
     * Get common activity item validation rules
     */
    private static function activityItemRules(string $allowedTypes = 'predefined'): array
    {
        return [
            'activities.*.id' => 'required|string',
            'activities.*.name' => 'required|string',
            'activities.*.price' => 'required|numeric',
            'activities.*.guests' => 'required|integer|min:1',
            'activities.*.type' => 'required|string|in:' . $allowedTypes,
        ];
    }

    /**
     * Get activities validation rules
     */
    public static function activitiesRules(array $additionalRules = []): array
    {
        return array_merge([
            'activities' => 'required|array',
        ], self::activityItemRules(), $additionalRules);
    }

    /**
     * Get admin CRUD validation rules
     */
    public static function crudRules(string $field = 'name', array $additionalRules = []): array
    {
        return array_merge([
            $field => 'required|string|max:255',
        ], $additionalRules);
    }

    /**
     * Get admin CRUD update validation rules
     */
    public static function crudUpdateRules(int $id, string $field = 'name', array $additionalRules = []): array
    {
        return array_merge([
            $field => 'required|string|max:255|unique:' . $field . 's,' . $field . ',' . $id,
        ], $additionalRules);
    }

    /**
     * Get staycation activities validation rules (activities are optional)
     */
    public static function staycationActivitiesRules(): array
    {
        return array_merge([
            'activities' => 'array', // Allow empty array for "proceed without activities"
        ], self::activityItemRules('predefined,custom'));
    }

    /**
     * Get dayvisit booking validation rules
     */
    public static function dayvisitBookingRules(): array
    {
        return array_merge(self::personalInfoRules(), [
            'booking_date' => 'sometimes|date|after:yesterday',
            'guests' => 'required|integer|min:1',
        ]);
    }

    /**
     * Get special event booking validation rules
     */
    public static function specialEventBookingRules(): array
    {
        return array_merge(self::personalInfoRules(), [
            'guests' => 'required|integer|min:1|max:100', // Higher limit for events
        ]);
    }

    /**
     * Get staycation dates validation rules
     */
    public static function staycationDatesRules(): array
    {
        return [
            'check_in' => 'required|date|after:yesterday',
            'check_out' => 'required|date|after:check_in',
        ];
    }

    /**
     * Get staycation POST validation rules
     */
    public static function staycationPostRules(): array
    {
        return [
            'check_in' => 'sometimes|date|after:yesterday',
            'check_out' => 'sometimes|date|after:check_in',
            'accommodations' => 'sometimes|array',
        ];
    }

    /**
     * Get availability check validation rules
     */
    public static function availabilityCheckRules(): array
    {
        return [
            'checkin' => 'required|date',
            'checkout' => 'required|date|after:checkin',
            'staycation_ids' => 'required|array',
            'staycation_ids.*' => 'integer',
        ];
    }
}