<?php

namespace App\Helpers;

class TimeHelper
{
    /**
     * Convert 24-hour format to 12-hour format with AM/PM
     * Accepts formats like: "08:00", "12:00", "14:30", etc.
     * Returns: "8:00 AM", "12:00 PM", "2:30 PM", etc.
     */
    public static function format24to12($time)
    {
        // If already in 12-hour format (contains AM/PM), return as-is
        if (strpos($time, 'AM') !== false || strpos($time, 'PM') !== false) {
            return $time;
        }

        // Parse the time
        try {
            $parsed = \DateTime::createFromFormat('H:i', $time);
            if (!$parsed) {
                return $time; // Return original if parsing fails
            }
            return $parsed->format('g:i A'); // g:i A = 12-hour format without leading zero on hours, with AM/PM
        } catch (\Exception $e) {
            return $time; // Return original on error
        }
    }

    /**
     * Format a session time range
     * Example: "08:00" and "11:00" → "8:00 AM - 11:00 AM"
     */
    public static function formatSessionTime($start, $end)
    {
        return self::format24to12($start) . ' - ' . self::format24to12($end);
    }
}
