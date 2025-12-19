<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayTypeDay extends Model
{
    protected $fillable = ['day_type_id', 'weekday'];

    public function dayType()
    {
        return $this->belongsTo(DayType::class);
    }
}
