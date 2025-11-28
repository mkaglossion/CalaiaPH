<?php

namespace App\Models\Traits;

trait HasImagesAndTexts
{
    public function swiperImages()
    {
        return $this->hasMany($this->getImageModelClass());
    }

    public function texts()
    {
        return $this->hasMany($this->getTextModelClass());
    }

    abstract protected function getImageModelClass(): string;
    abstract protected function getTextModelClass(): string;
}