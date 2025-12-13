<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaycationAccommodation extends Model
{
    protected $fillable = ['name', 'description', 'price', 'image_path', 'public_id'];

    // Accommodation texts
    public function accommodationTexts()
    {
        return $this->hasMany(AccommodationText::class, 'accommodation_id');
    }

    // Variants for this accommodation (many-to-many with staycations)
    public function variants()
    {
        return $this->belongsToMany(
            Staycation::class,
            'accommodation_variants',
            'staycation_accommodation_id', // foreign key on pivot pointing to accommodation
            'staycation_id'               // foreign key on pivot pointing to staycation
        )->withPivot('id'); // optional, if you want pivot id
    }

    public function accommodationVariants()
    {
        return $this->hasMany(AccommodationVariant::class, 'staycation_accommodation_id');
    }
}

