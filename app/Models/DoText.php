<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoText extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'text'];
}
