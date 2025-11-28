<?php

namespace App\Models;

class ActivityText extends BaseRelatedText
{
    protected $fillable = ['activity_id', 'section', 'title', 'content'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    protected function getParentModelClass(): string
    {
        return Activity::class;
    }
}
