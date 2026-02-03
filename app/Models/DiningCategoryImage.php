<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiningCategoryImage extends Model
{
    protected $table = 'dining_category_images';

    protected $fillable = ['category', 'image_path', 'public_id'];
}
