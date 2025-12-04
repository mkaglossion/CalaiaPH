<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['name'];

    public function swiperImages()
    {
        return $this->hasMany(EventSwiperImage::class);
    }

    public function texts()
    {
        return $this->hasMany(EventText::class);
    }
}
