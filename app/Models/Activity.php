<?php

namespace App\Models;

class Activity extends ContentModel
{
    protected $fillable = ['name'];

    // 🔥 Reverse relation for Day Tour Packages
    public function dayTours()
    {
        return $this->belongsToMany(DayTour::class, 'day_tour_activity');
    }

    protected function getImageModelClass(): string
    {
        return ActivitySwiperImage::class;
    }

    protected function getTextModelClass(): string
    {
        return ActivityText::class;
    }
}
