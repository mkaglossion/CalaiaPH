<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['date', 'name'];

    protected static function boot()
    {
        parent::boot();

        // Broadcast pricing update when holiday is saved or updated
        static::saved(function ($holiday) {
            broadcast(new \App\Events\PricingUpdated())->toOthers();
        });

        // Broadcast pricing update when holiday is deleted
        static::deleted(function ($holiday) {
            broadcast(new \App\Events\PricingUpdated())->toOthers();
        });
    }

    public function pricingConfigs()
    {
        return $this->hasMany(PricingConfig::class);
    }
}
