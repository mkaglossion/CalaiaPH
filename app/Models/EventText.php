<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventText extends Model
{
    protected $fillable = ['event_id', 'section', 'title', 'content'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
