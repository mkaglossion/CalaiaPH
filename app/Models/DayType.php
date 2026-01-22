<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayType extends Model
{
    protected $fillable = ['name'];

    protected static function boot()
    {
        parent::boot();

        // Broadcast pricing update when day type is saved or updated
        static::saved(function ($dayType) {
            broadcast(new \App\Events\PricingUpdated())->toOthers();
        });

        // Broadcast pricing update when day type is deleted
        static::deleted(function ($dayType) {
            broadcast(new \App\Events\PricingUpdated())->toOthers();
        });
    }

    public function days()
    {
        return $this->hasMany(DayTypeDay::class);
    }

    public function pricingConfigs()
    {
        return $this->hasMany(PricingConfig::class);
    }
}
