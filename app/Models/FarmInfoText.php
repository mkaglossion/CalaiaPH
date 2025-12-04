<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmInfoText extends Model
{
    protected $fillable = ['farm_info_id', 'section', 'title', 'content'];

    public function farmInfo()
    {
        return $this->belongsTo(FarmInfo::class);
    }
}
