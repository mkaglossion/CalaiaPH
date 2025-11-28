<?php

namespace App\Models;

class FarmInfo extends ContentModel
{
    protected $fillable = ['name'];

    protected function getImageModelClass(): string
    {
        return FarmInfoSwiperImage::class;
    }

    protected function getTextModelClass(): string
    {
        return FarmInfoText::class;
    }
}
