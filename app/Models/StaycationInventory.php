<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaycationInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'staycation_id',
        'date',
        'available_quantity',
    ];

    protected $casts = [
        'date' => 'date',
        'available_quantity' => 'integer',
    ];

    public function staycation()
    {
        return $this->belongsTo(Staycation::class);
    }
}
