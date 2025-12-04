<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaycationText extends Model
{
    protected $fillable = ['staycation_id', 'section', 'title', 'content'];

    public function staycation()
    {
        return $this->belongsTo(Staycation::class);
    }
}
