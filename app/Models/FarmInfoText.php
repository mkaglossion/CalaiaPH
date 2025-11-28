<?php

namespace App\Models;

class FarmInfoText extends BaseRelatedText
{
    protected $fillable = ['farm_info_id', 'section', 'title', 'content'];

    public function farmInfo()
    {
        return $this->belongsTo(FarmInfo::class);
    }

    protected function getParentModelClass(): string
    {
        return FarmInfo::class;
    }
}
