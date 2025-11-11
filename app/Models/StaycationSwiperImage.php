<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaycationSwiperImage extends Model
{
    protected $fillable = ['staycation_id', 'title', 'image_path'];

    public function staycation()
    {
        return $this->belongsTo(Staycation::class);
    }
}

