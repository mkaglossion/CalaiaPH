<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'region_id',
        'province_id',
        'city_id',
        'barangay_id',
        'street_address',
        'subdivision',
        'zip_code',
        'landmark',
    ];

    protected $appends = [
        'full_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function getFullAddressAttribute(): string
    {
        $line1 = trim(implode(', ', array_filter([
            $this->street_address,
            $this->subdivision,
        ])));

        $line2 = trim(implode(', ', array_filter([
            optional($this->barangay)->name,
            optional($this->city)->name,
            optional($this->province)->name,
            optional($this->region)->name,
            $this->zip_code,
        ])));

        $line3 = $this->landmark ? ('Landmark: ' . $this->landmark) : null;

        return trim(implode("\n", array_filter([$line1, $line2, $line3])));
    }
}

