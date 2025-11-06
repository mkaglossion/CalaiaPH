<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwiperImage extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'image_path', 'page'];
}