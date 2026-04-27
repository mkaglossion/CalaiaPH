<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'name',
        'description',
        'duration',
        'price',
        'availability',
        'image',
        'category',
        'is_active',
        'sort_order'
    ];

    public function swiperImages()
    {
        return $this->hasMany(ActivitySwiperImage::class);
    }

    public function texts()
    {
        return $this->hasMany(ActivityText::class);
    }

    // 🔥 Reverse relation for Day Tour Packages
    public function dayTours()
    {
        return $this->belongsToMany(DayTour::class, 'day_tour_activity');
    }

    // 🔥 Relation for Activity Packages
    public function activityPackages()
    {
        return $this->belongsToMany(ActivityPackage::class, 'activity_package_items')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
