<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiningReservation extends Model
{
    protected $fillable = [
        'food_id',
        'food_name',
        'meal_period',
        'reservation_date',
        'reservation_time',
        'adults',
        'first_name',
        'last_name',
        'phone',
        'email',
        'special_requests',
        'status',
        'payment_status',
        'payment_method',
        'payment_notes',
        'total_amount'
    ];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }
}
