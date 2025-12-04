<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayTour extends Model
{
    protected $fillable = ['name', 'description', 'price'];

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'day_tour_activity');
    }
}
