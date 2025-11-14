<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayPackageText extends Model
{
    protected $fillable = ['package_id', 'section', 'title', 'content'];

    public function daytour()
    {
        return $this->belongsTo(DayTour::class, 'package_id');
    }
}
