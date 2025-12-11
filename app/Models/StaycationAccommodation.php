<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaycationAccommodation extends Model
{
    protected $fillable = ['name', 'description', 'price'];

    public function accommodationVariants()
    {
        return $this->belongsToMany(Staycation::class, 'accommodation_variants');
    }

    // Texts related to this accommodation
    public function accommodationTexts()
    {
        return $this->hasMany(AccommodationText::class, 'accommodation_id');
    }
}
