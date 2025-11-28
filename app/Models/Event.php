<?php

namespace App\Models;

class Event extends ContentModel
{
    protected $fillable = ['name'];

    protected function getImageModelClass(): string
    {
        return EventSwiperImage::class;
    }

    protected function getTextModelClass(): string
    {
        return EventText::class;
    }
}
