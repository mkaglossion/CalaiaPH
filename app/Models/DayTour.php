<?php

namespace App\Models;

class DayTour extends ContentModel
{
    protected $fillable = ['name', 'description', 'price'];

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'day_tour_activity');
    }

    protected function getImageModelClass(): string
    {
        return DayTourSwiperImage::class;
    }

    protected function getTextModelClass(): string
    {
        return DayTourText::class;
    }
}
