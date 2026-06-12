<?php

namespace App\Services;

class ValidationService
{
    public static function bookingRules($maxGuests = 50)
    {
        return [
            'type' => 'required|string|in:day-visit,room',
            'package' => 'required|string',
            'price' => 'required|numeric|min:0',
            'booking_date' => 'required|date|after:yesterday',
            'guests' => 'required|integer|min:1|max:' . $maxGuests,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'special_requests' => 'nullable|string|max:1000',
        ];
    }

    public static function activitiesRules()
    {
        return [
            'activities' => 'array',
            'activities.*.id' => 'required|string',
            'activities.*.name' => 'required|string',
            'activities.*.price' => 'required|numeric',
            'activities.*.guests' => 'required|integer|min:1',
            'activities.*.type' => 'required|string|in:predefined,custom',
        ];
    }

    public static function staycationActivitiesRules()
    {
        return [
            'activities' => 'nullable|array',
            'activities.*.id' => 'required|string',
            'activities.*.name' => 'required|string',
            'activities.*.price' => 'required|numeric',
            'activities.*.guests' => 'required|integer|min:1',
            'activities.*.type' => 'required|string|in:predefined,custom',
        ];
    }

    public static function paymentRules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'payment_method' => 'nullable|string|in:paymongo,qr,gcash,paymaya,bank,qrph,grab_pay,billease',
            'special_requests' => 'nullable|string|max:1000',
            'cancellation_policy' => 'nullable|string|in:standard,flexible,nonrefundable',
            'camp_waiver_participant_name' => 'nullable|string|max:255',
            'camp_waiver_birth_date' => 'nullable|date',
            'camp_waiver_gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
            'camp_waiver_address' => 'nullable|string|max:500',
            'camp_waiver_emergency_name' => 'nullable|string|max:255',
            'camp_waiver_emergency_phone' => 'nullable|string|max:20',
            'camp_waiver_parent_guardian_name' => 'nullable|string|max:255',
            'camp_waiver_relationship' => 'nullable|string|max:255',
            'camp_waiver_medical_condition' => 'nullable|string|max:1000',
            'camp_waiver_signature_name' => 'nullable|string|max:255',
            'camp_waiver_signature_date' => 'nullable|date',
            'camp_waiver_parent_signature_name' => 'nullable|string|max:255',
            'camp_waiver_child_ward_name' => 'nullable|string|max:255',
            'camp_waiver_parent_signature_date' => 'nullable|date',
            'terms_accepted' => 'sometimes|accepted',
            'terms_and_policy_accepted' => 'sometimes|accepted',
            'camp_waiver_accepted' => 'sometimes|accepted',
        ];
    }

    public static function staycationPostRules()
    {
        return [
            'check_in' => 'nullable|date|after:yesterday',
            'check_out' => 'nullable|date|after:check_in',
            'accommodations' => 'nullable|array',
            'accommodations.*.staycation' => 'required|array',
            'accommodations.*.adults' => 'required|integer|min:1',
            'accommodations.*.children' => 'nullable|integer|min:0',
            'accommodations.*.pet_agreement' => 'nullable|boolean',
            'accommodations.*.num_pets' => 'nullable|integer|min:0',
            'accommodations.*.has_breakfast' => 'nullable|boolean',
        ];
    }

    public static function staycationDatesRules()
    {
        return [
            'checkin' => 'required|date|after:yesterday',
            'checkout' => 'required|date|after:checkin',
        ];
    }

    public static function availabilityCheckRules()
    {
        return [
            'checkin' => 'required|date|after:yesterday',
            'checkout' => 'required|date|after:checkin',
            'staycation_ids' => 'required|array',
            'staycation_ids.*' => 'integer',
        ];
    }

    public static function dayvisitBookingRules()
    {
        return [
            'booking_date' => 'nullable|date|after:yesterday',
            'guests' => 'required|integer|min:1|max:50',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'special_requests' => 'nullable|string|max:1000',
        ];
    }

    public static function specialEventBookingRules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'guests' => 'required|integer|min:1|max:50',
            'special_requests' => 'nullable|string|max:1000',
        ];
    }
}
