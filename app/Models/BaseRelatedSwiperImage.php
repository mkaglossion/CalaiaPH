<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToParent;

abstract class BaseRelatedSwiperImage extends Model
{
    use BelongsToParent;

    protected $fillable = ['title', 'image_path', 'public_id'];

    abstract protected function getParentModelClass(): string;
}