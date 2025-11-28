<?php

namespace App\Models;

class ActivitySwiperImage extends BaseRelatedSwiperImage
{
    protected $fillable = ['activity_id', 'title', 'image_path', 'public_id'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    protected function getParentModelClass(): string
    {
        return Activity::class;
    }
}

