<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayTour extends Model
{
    protected $fillable = ['name', 'description', 'price', 'image'];

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'day_tour_activity');
    }

    // Additional swiper images for modal (excludes package image)
    public function swiperImages()
    {
        return $this->hasMany(DayTourSwiperImage::class, 'daytour_id')->where('image_path', '!=', $this->image);
    }
}
