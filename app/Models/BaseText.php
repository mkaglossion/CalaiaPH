<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class BaseText extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'text'];
}