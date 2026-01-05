<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staycation extends Model
{
    protected $fillable = ['name', 'quantity', 'price_configs'];

    protected $casts = [
        'price_configs' => 'array',
    ];

    public function texts()
    {
        return $this->hasMany(StaycationText::class);
    }

    public function swiperImages()
    {
        return $this->hasMany(StaycationSwiperImage::class);
    }

    // Accommodations via variants
    public function staycationAccommodations()
    {
        return $this->belongsToMany(
            StaycationAccommodation::class,
            'accommodation_variants',
            'staycation_id',
            'staycation_accommodation_id'
        );
    }
}

