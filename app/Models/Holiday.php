<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['date', 'name'];

    public function pricingConfigs()
    {
        return $this->hasMany(PricingConfig::class);
    }
}
