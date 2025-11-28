<?php

namespace App\Models;

class EventText extends BaseRelatedText
{
    protected $fillable = ['event_id', 'section', 'title', 'content'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    protected function getParentModelClass(): string
    {
        return Event::class;
    }
}
