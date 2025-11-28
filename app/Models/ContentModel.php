<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasImagesAndTexts;

abstract class ContentModel extends Model
{
    use HasImagesAndTexts;

    protected $fillable = ['name'];

    abstract protected function getImageModelClass(): string;

    abstract protected function getTextModelClass(): string;
}