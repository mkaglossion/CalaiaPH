<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSwiperImage extends Model
{
    protected $fillable = ['event_id', 'title', 'image_path'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}

