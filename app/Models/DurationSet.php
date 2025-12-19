<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DurationSet extends Model
{
    protected $fillable = ['name', 'duration'];

    public function pricingConfigs()
    {
        return $this->hasMany(PricingConfig::class);
    }
}
