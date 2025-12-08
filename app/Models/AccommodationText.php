<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationText extends Model
{
    protected $fillable = ['accommodation_id', 'section', 'title', 'content'];

    public function daytour()
    {
        return $this->belongsTo(StaycationAccommodation::class, 'accommodation_id');
    }
}
