<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingConfig extends Model
{
    protected $fillable = [
        'unit_type', 'peak_type_id', 'peak_period_id', 'holiday_id', 'day_type_id', 'duration_set_id', 'price'
    ];

    /**
     * Get the peak type associated with this pricing config.
     */
    public function peakType()
    {
        return $this->belongsTo(PeakType::class);
    }

    /**
     * Get the peak period associated with this pricing config.
     */
    public function peakPeriod()
    {
        return $this->belongsTo(PeakPeriod::class);
    }

    /**
     * Get the holiday associated with this pricing config.
     */
    public function holiday()
    {
        return $this->belongsTo(Holiday::class);
    }

    /**
     * Get the day type associated with this pricing config.
     */
    public function dayType()
    {
        return $this->belongsTo(DayType::class);
    }

    /**
     * Get the duration set associated with this pricing config.
     */
    public function durationSet()
    {
        return $this->belongsTo(DurationSet::class);
    }
}
