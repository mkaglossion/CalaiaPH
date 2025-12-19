<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeakPeriod extends Model
{
    protected $fillable = ['peak_type_id', 'start_date', 'end_date'];

    public function peakType()
    {
        return $this->belongsTo(PeakType::class);
    }

    public function pricingConfigs()
    {
        return $this->hasMany(PricingConfig::class);
    }
}
