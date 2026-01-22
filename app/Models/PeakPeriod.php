<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeakPeriod extends Model
{
    protected $fillable = ['peak_type_id', 'start_date', 'end_date', 'name'];

    protected static function boot()
    {
        parent::boot();

        // Broadcast pricing update when peak period is saved or updated
        static::saved(function ($peakPeriod) {
            broadcast(new \App\Events\PricingUpdated())->toOthers();
        });

        // Broadcast pricing update when peak period is deleted
        static::deleted(function ($peakPeriod) {
            broadcast(new \App\Events\PricingUpdated())->toOthers();
        });
    }

    public function peakType()
    {
        return $this->belongsTo(PeakType::class);
    }

    public function pricingConfigs()
    {
        return $this->hasMany(PricingConfig::class);
    }
}

