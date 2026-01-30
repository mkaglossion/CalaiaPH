<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayTourSwiperImage extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'image_path', 'public_id', 'day_tour_id', 'package_id', 'daytour_id'];

    public function dayTour()
    {
        return $this->belongsTo(DayTour::class, 'daytour_id');
    }
}