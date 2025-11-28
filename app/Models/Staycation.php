<?php

namespace App\Models;

class Staycation extends ContentModel
{
    protected $fillable = ['name'];

    protected function getImageModelClass(): string
    {
        return StaycationSwiperImage::class;
    }

    protected function getTextModelClass(): string
    {
        return StaycationText::class;
    }
}
