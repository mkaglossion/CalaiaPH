<?php

namespace App\Models;

class MenuSwiperImage extends BaseRelatedSwiperImage
{
    protected $fillable = ['menu_id', 'title', 'image_path', 'public_id'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    protected function getParentModelClass(): string
    {
        return Menu::class;
    }
}

