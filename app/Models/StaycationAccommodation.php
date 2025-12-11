<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaycationAccommodation extends Model
{
    protected $fillable = ['name', 'description', 'price', 'image_path', 'public_id'];

    public function accommodationVariants()
    {
        return $this->belongsToMany(Staycation::class, 'accommodation_variants');
    }
}
