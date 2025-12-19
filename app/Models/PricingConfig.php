<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingConfig extends Model
{
    protected $fillable = [
        'peak_period_id', 'holiday_id', 'duration_set_id', 'day_type_id', 'price'
    ];

    public function peakPeriod()
    {
        return $this->belongsTo(PeakPeriod::class);
    }

    public function holiday()
    {
        return $this->belongsTo(Holiday::class);
    }

    public function durationSet()
    {
        return $this->belongsTo(DurationSet::class);
    }

    public function dayType()
    {
        return $this->belongsTo(DayType::class);
    }
}
