<?php

namespace App\Models;

class EventSwiperImage extends BaseRelatedSwiperImage
{
    protected $fillable = ['event_id', 'title', 'image_path', 'public_id'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    protected function getParentModelClass(): string
    {
        return Event::class;
    }
}

