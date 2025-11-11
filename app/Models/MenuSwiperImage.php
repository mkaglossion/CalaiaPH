<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuSwiperImage extends Model
{
    protected $fillable = ['menu_id', 'title', 'image_path'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}

