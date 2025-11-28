<?php

namespace App\Models;

class StaycationText extends BaseRelatedText
{
    protected $fillable = ['staycation_id', 'section', 'title', 'content'];

    public function staycation()
    {
        return $this->belongsTo(Staycation::class);
    }

    protected function getParentModelClass(): string
    {
        return Staycation::class;
    }
}
