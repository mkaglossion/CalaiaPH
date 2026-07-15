<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait HasSortableOrder
{
    public static function bootHasSortableOrder(): void
    {
        static::creating(function ($model): void {
            if (!Schema::hasColumn($model->getTable(), 'sort_order')) {
                return;
            }

            if (!empty($model->sort_order)) {
                return;
            }

            $model->sort_order = ((int) $model->newQuery()->max('sort_order')) + 1;
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        if (Schema::hasColumn($this->getTable(), 'sort_order')) {
            return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'asc')->orderBy('id', 'asc');
        }

        return $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
    }
}
