<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staycation extends Model
{
    protected $fillable = ['name'];

    public function swiperImages()
    {
        return $this->hasMany(StaycationSwiperImage::class);
    }

    public function texts()
    {
        return $this->hasMany(StaycationText::class);
    }

    // 🔥 Reverse relation for Day Tour Packages
    public function staycationAccommodations()
    {
        return $this->belongsToMany(StaycationAccommodation::class, 'accommodation_variants');
    }
}
