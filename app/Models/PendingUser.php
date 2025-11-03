<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingUser extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'username',
        'password',
        'token',
    ];
}
