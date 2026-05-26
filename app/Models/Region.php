<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    public const NCR_PSGC = '1300000000';

    protected $fillable = [
        'psgc_code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function customerAddresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function isNcr(): bool
    {
        return $this->psgc_code === self::NCR_PSGC;
    }
}

