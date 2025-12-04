<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodText extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'text'];
}
