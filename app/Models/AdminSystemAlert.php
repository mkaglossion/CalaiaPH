<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeUnresolvedCritical($query)
    {
        return $query->unresolved()->where('severity', 'critical');
    }
}
