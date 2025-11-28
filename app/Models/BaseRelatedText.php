<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToParent;

abstract class BaseRelatedText extends Model
{
    use BelongsToParent;

    protected $fillable = ['title', 'content'];

    abstract protected function getParentModelClass(): string;
}