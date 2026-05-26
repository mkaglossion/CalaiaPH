<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'psgc_code',
        'name',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function customerAddresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }
}

