<?php

namespace App\Models;

class StaycationSwiperImage extends BaseRelatedSwiperImage
{
    protected $fillable = ['staycation_id', 'title', 'image_path', 'public_id'];

    public function staycation()
    {
        return $this->belongsTo(Staycation::class);
    }

    protected function getParentModelClass(): string
    {
        return Staycation::class;
    }
}

