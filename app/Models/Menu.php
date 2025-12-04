<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['name'];

    public function swiperImages()
    {
        return $this->hasMany(MenuSwiperImage::class);
    }

    public function texts()
    {
        return $this->hasMany(MenuText::class);
    }
}
