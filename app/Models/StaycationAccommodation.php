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
}
