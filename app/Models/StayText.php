<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StayText extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'text'];

    public function getTitleAttribute()
    {
        return $this->label;
    }

    public function getContentAttribute()
    {
        return $this->text;
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['label'] = $value;
    }

    public function setContentAttribute($value)
    {
        $this->attributes['text'] = $value;
    }
}
