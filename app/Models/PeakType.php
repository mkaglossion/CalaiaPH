<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeakType extends Model
{
    protected $fillable = ['name', 'priority'];

    public function periods()
    {
        return $this->hasMany(PeakPeriod::class);
    }
}
