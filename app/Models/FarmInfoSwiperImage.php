<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FarmInfoSwiperImage extends Model
{
    protected $fillable = ['farm_info_id', 'title', 'image_path', 'public_id'];

    public function farmInfo()
    {
        return $this->belongsTo(FarmInfo::class);
    }
}

