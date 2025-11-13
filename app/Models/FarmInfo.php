<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmInfo extends Model
{
    protected $fillable = ['name'];

    public function swiperImages()
    {
        return $this->hasMany(FarmInfoSwiperImage::class);
    }

    public function texts()
    {
        return $this->hasMany(FarmInfoText::class);
    }
}
