<?php

namespace App\Models\Traits;

trait BelongsToParent
{
    public function getParentRelationship()
    {
        return $this->belongsTo($this->getParentModelClass());
    }

    abstract protected function getParentModelClass(): string;
}