<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class BaseSwiperImage extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'image_path', 'public_id'];
}