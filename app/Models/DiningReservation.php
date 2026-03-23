<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiningReservation extends Model
{
    protected $fillable = [
        'food_id',
        'meal_period',
        'reservation_date',
        'reservation_time',
        'adults',
        'first_name',
        'last_name',
        'phone',
        'email',
        'special_requests',
        'status'
    ];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }
}
