<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayTourText extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'daytour_id'];

    public function dayTour()
    {
        return $this->belongsTo(DayTour::class);
    }
}
