<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivitySwiperImage extends Model
{
    protected $fillable = ['activity_id', 'title', 'image_path', 'public_id'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}

