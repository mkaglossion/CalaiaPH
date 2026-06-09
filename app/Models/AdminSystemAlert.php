<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AdminSystemAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'severity',
        'message',
        'data',
        'resolved_at',
    ];

    protected $casts = [
        'data' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function resolve()
    {
        $this->update(['resolved_at' => now()]);
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeUnresolvedCritical(Builder $query): Builder
    {
        return $query->unresolved()->where('severity', 'critical');
    }

    public function scopeUnresolvedForDashboard(Builder $query): Builder
    {
        return $query->unresolved()
            ->orderByRaw("CASE severity WHEN 'critical' THEN 3 WHEN 'warning' THEN 2 WHEN 'info' THEN 1 ELSE 0 END DESC")
            ->orderByDesc('created_at');
    }
}
