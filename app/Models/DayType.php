<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayType extends Model
{
    protected $fillable = ['name'];

    public function days()
    {
        return $this->hasMany(DayTypeDay::class);
    }

    public function pricingConfigs()
    {
        return $this->hasMany(PricingConfig::class);
    }
}
