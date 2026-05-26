<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'psgc_code',
        'name',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function customerAddresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }
}

