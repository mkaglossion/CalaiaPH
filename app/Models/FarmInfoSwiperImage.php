<?php

namespace App\Models;

class FarmInfoSwiperImage extends BaseRelatedSwiperImage
{
    protected $fillable = ['farm_info_id', 'title', 'image_path', 'public_id'];

    public function farmInfo()
    {
        return $this->belongsTo(FarmInfo::class);
    }

    protected function getParentModelClass(): string
    {
        return FarmInfo::class;
    }
}

