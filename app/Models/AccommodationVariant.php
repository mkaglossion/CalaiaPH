<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccommodationVariant extends Model
{
    // The accommodation this variant belongs to
    public function staycationAccommodation()
    {
        return $this->belongsTo(StaycationAccommodation::class);
    }

    // Optionally, if variants are tied to a main staycation as well
    public function staycation()
    {
        return $this->belongsTo(Staycation::class);
    }

    // Helper to get texts via the staycation
    public function getText($title)
    {
        return $this->staycation?->texts->firstWhere('title', $title)?->content;
    }

    // Get the text content for this variant by title
    public function text($title)
    {
        return $this->staycation?->texts->firstWhere('title', $title)?->content;
    }
}


