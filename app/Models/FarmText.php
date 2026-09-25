<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmText extends Model
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

    public function setTitleAttribute(mixed $value): void
    {
        $this->attributes['label'] = $value;
    }

    public function setContentAttribute(mixed $value): void
    {
        $this->attributes['text'] = $value;
    }
}
