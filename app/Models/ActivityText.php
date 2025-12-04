<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityText extends Model
{
    protected $fillable = ['activity_id', 'section', 'title', 'content'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
