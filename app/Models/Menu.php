<?php

namespace App\Models;

class Menu extends ContentModel
{
    protected $fillable = ['name'];

    protected function getImageModelClass(): string
    {
        return MenuSwiperImage::class;
    }

    protected function getTextModelClass(): string
    {
        return MenuText::class;
    }
}
